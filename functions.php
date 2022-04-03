<?php
namespace k1;

/**
 * Load and initialize k1kit if not done already
 *
 */
if (!class_exists('\k1\App')) {
  if (is_dir(WP_PLUGIN_DIR . '/k1kit')) {
    require_once WP_PLUGIN_DIR . '/k1kit/src/php/init.php';
  } else {
    throw new \Exception("k1kit wasn't found. The theme can't be used without it.");
  }
}

require_once 'classes/class.vite.php';

$app = App::init([
  'blocks' => glob(__DIR__ . '/blocks/*.php'),
  'templates' => glob(__DIR__ . '/templates/*.php'),
  // 'languageSlugs' => ['en'], // managed by polylang
  'manifests' => [],
]);

$app->manifests['vite'] = new \k1\Vite(__DIR__ . '/dist/manifest.json');


foreach (glob(dirname(__FILE__) . "/lib/*.php") as $filename) {
  require_once($filename);
}

foreach (glob(dirname(__FILE__) . "/classes/class.*.php") as $filename) {
  require_once($filename);
}

foreach (glob(dirname(__FILE__) . "/api/*.php") as $filename) {
  require_once($filename);
}

/**
 * This returns a which has been created at the top of this page.
 */
function app() {
  return App::init();
}

function debug($data) {
  echo "<pre>";
  echo htmlspecialchars(var_export($data, true));
  echo "</pre>";
}


function injectViteIfDev() {
  $app = app();

  if ($app->manifests['vite']->isDev()) { ?>
    <script type="module" src="http://localhost:8888/@vite/client"></script>
    <script type="module">
      import RefreshRuntime from 'http://localhost:8888/@react-refresh'
      RefreshRuntime.injectIntoGlobalHook(window)
      window.$RefreshReg$ = () => {}
      window.$RefreshSig$ = () => (type) => type
      window.__vite_plugin_react_preamble_installed__ = true
    </script><?php
  }
}

add_action('admin_head', '\k1\injectViteIfDev');
add_action('wp_head', '\k1\injectViteIfDev');

/**
 * Pass useful data to the frontend, instead of crawling these from the DOM.
 * Path can be used for dynamic imports, and wpurl for making HTTP requests,
 * if you absolutely have to have absolute urls in your code.
 */
$localizeData = [
  'lang' => $app->i18n->getLanguage(),
  'path' => get_stylesheet_directory_uri(),
  'wpurl' => get_site_url(),
  'i18n' => $strings,
];
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );

add_action('wp_enqueue_scripts', function() use ($app, $localizeData) {
  $build = $app->manifests['vite'];

  $handles = $build->enqueue('src/client.tsx', ['react']);
  wp_localize_script($handles[0], 'wptheme', $localizeData);
});

add_action('admin_enqueue_scripts', function() use ($app, $localizeData) {
  $build = $app->manifests['vite'];

  $handles = $build->enqueue('src/admin.tsx', ['react']);
  wp_localize_script($handles[0], 'wptheme', $localizeData);
});


add_action('allowed_block_types_all', function() use ($app) {
  $core = [
      'core/block', // required for reusable blocks
      'core/template', // see above

      // Core has like 200 different blocks and 190 of them are trash.
			'core/paragraph',
			'core/image',
			'core/heading',
			'core/list',
			'core/nextpage',
			'core/separator',
      'core/shortcode',
			'core/embed',
      'core/columns',
  ];

  $acfBlocks = [];

  foreach ($app->getBlocks() as $block) {

    $acfBlocks[] = 'acf/' . strtolower($block->getName());
  }

  return array_merge($core, $acfBlocks);
}, PHP_INT_MAX);

add_action('rest_api_init', function() {
  (new \k1\Routes\PostListing())->registerRoutes();
});


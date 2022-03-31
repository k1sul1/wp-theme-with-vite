<?php
/**
 * The header template. Included by get_header().
 */
namespace k1;

use \k1\Media;
use \k1\Templates as T;

$app = app();

?>
<!doctype html>
<html <?php language_attributes(); ?>>
  <head>
    <meta name="viewport" content="initial-scale=1">

    <?php
    if ($app->manifests['vite']->isDev()) { ?>
    <!--  Bad idea -->
    <!-- <base href="http://localhost:8888"> -->
    <script type="module" src="http://localhost:8888/@vite/client"></script>
    <script type="module">
      import RefreshRuntime from 'http://localhost:8888/@react-refresh'
      RefreshRuntime.injectIntoGlobalHook(window)
      window.$RefreshReg$ = () => {}
      window.$RefreshSig$ = () => (type) => type
      window.__vite_plugin_react_preamble_installed__ = true
    </script>
    <?php
    } ?>


    <?php wp_head(); ?>
  </head>
  <?php
  global $is_anon_user;
  $is_anon_user = !is_user_logged_in();
  ?>
  <body <?php body_class([
    !$is_anon_user ? 'user-logged-in' : 'user-not-logged-in',
  ]);?>>

  <!-- Prevent FOUC in firefox, see https://bugzilla.mozilla.org/show_bug.cgi?id=1404468#c68 -->
  <script>0</script>


  <a class="skip-link sr-text" href="#content">
    Skip to content
  </a>


  <header class="k1-header k1-header--site headroom headroom--top">
    <div class="k1-header__menuContainer k1-container">
      <?php
      if (\has_custom_logo()) {
        \the_custom_logo();
      } ?>

    <nav class="k1-navigation k1-navigation--desktop k1-scheme--base-invert">
      <div class="k1-container">
        <?php if (\has_nav_menu('header-menu')) {
          \wp_nav_menu([
            "theme_location" => "header-menu",
          ]);
        } else {
          echo "<p>Header menu is empty.</p>";
        }?>
      </div>
    </nav>

    <?php
    $name = wp_get_nav_menu_name('header-menu');
    $items = wp_get_nav_menu_items($name);
    $data = wp_json_encode(compact("name", "items"));
    ?>

    <div class="mobile-menu" data-data='<?=esc_attr($data)?>'>
      <!-- Created with React using the data supplied in the attributes -->
    </div>


    </div>

    <script>
      (function() {
        // WP does not allow placing class on the link element itself. This runs immediately after menu has been outputted, before actual rendering.

        // Lighthouse will probably hate this but it's mutual. This has negligible effect on performance.
        var btns = document.querySelectorAll('.k1-navigation li.button')

        for (var i = 0; i < btns.length; i++) {
          var btn = btns[i]

          // btn.classList.remove('button')
          btn.children[0].classList.add('k1-button')
          btn.children[0].classList.add('fill')
        }

        function fixVh() {
          // Browsers still can't handle vh units sanely. Better make our own vh.
          var vh = window.innerHeight * 0.01
          document.documentElement.style.setProperty('--vh', vh + 'px')
        }

        window.addEventListener('resize', fixVh)
        fixVh()
      })()
    </script>

  </header>


  <main id="content">

<?php
/**
 * ACF related filters and functions live here.
 * If it's a template tag, it doesn't belong here.
 *
 * These provide dynamic values to ACF fields like the scheme.
 */

namespace k1\ACF;

if (is_admin()) {
  // This is an example of translating ACF labels
  // $populator = function($field) use ($options) {
  //   $field['instructions'] = \k1\app()->i18n->getText("ACF: Avoid hiding");
  //   $field['choices'] = $options;

  //   return $field;
  // };

  // add_filter('acf/load_field/key=field_5d16385be0a6b', $populator);


  $populator = function($field) {
    $templates = \k1\getPostListTemplateList();
    $names = [];

    // ACF formatting requires this. The actual namespaced name ($template) has no meaning here.
    foreach ($templates as $name => $template) {
      $names[$name] = $name;
    }

    $field['choices'] = $names;

    return $field;
  };

  // PostListing template field
  add_filter('acf/load_field/key=field_6177fa3360bb3', $populator);

  if (\function_exists('acf_add_options_page')) {
    // \acf_add_options_page([
    //   'page_title' => 'Generic block settings',
    //   'menu_title' => 'Generic block settings',
    //   'parent_slug' => 'options-general.php',
    // ]);

    // Language spesific options pages.
    // You need to create a field group for each language, cloning from another.
    add_action('init', function() {
      $app = \k1\app();

      foreach (($app->i18n->getLanguages()) as $lang) {
        $lang = strtoupper($lang);

        \acf_add_options_page([
          'page_title' => "$lang settings",
          'menu_title' => "$lang settings",
          'parent_slug' => 'options-general.php',
        ]);
      }
    });
  }
}

/**
 * ACF Extended
 */
add_action('acfe/init', function (){
  // Enable Single Meta
  acfe_update_setting('modules/single_meta', true);

  acfe_update_setting('dev', !\k1\isProd());
});


<?php

namespace k1;

$app = app();
$strings = [
  'Font-size: Default' => 'Default',
  'Font-size: Much smaller' => 'Much smaller',
  'Font-size: Smaller' => 'Smaller',
  'Font-size: Normal' => 'Normal',
  'Font-size: Large' => 'Large',

  'Pagination: Previous' => 'Previous',
  'Pagination: Next' => 'Next',
  'Pagination: First' => 'First',
  'Pagination: Last' => 'Last',

  'Title: News' => 'News',
  'Title: Category' => 'Category',
  'Title: Tag' => 'Tag',
  'Title: Archive' => 'Archive',
  'Title: 404' => 'You took a wrong turn somewhere, 404!',
  'Title: Blog Subheading' => 'Subheading',
  'Title: Blog Heading' => 'Blog',

  'Breadcrumb: Home' => 'Home',
  'Placeholder: Find from page' => 'Find from page',

  'PostListFetchError' => 'Something went wrong! Try doing that again in a moment.',
  'PostListJsonError' => 'Something is wrong. We\'re working on the problem. Try again later.',

  'post_tag' => 'Tag',
  'category' => 'Category',
  'No posts found' => 'Oh no! No posts found. Try something else?',
  'Loading' => 'Loading',
];

foreach ($strings as $k => $v) {
  $app->i18n->registerString($k, $v);

  $strings[$k] = $app->i18n->getText($k);
}

add_action('rest_api_init', function() {
  if (function_exists('pll_default_language')) {
    // Set the language in api requests
    // https://github.com/polylang/polylang/issues/160#issuecomment-345991147

    $defaultLanguage = pll_default_language();
    $languages = pll_languages_list();
    $getRequestLanguage = \filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_STRING);
    $requestLanguage = $getRequestLanguage? $getRequestLanguage : 'fi' ;
    $language = in_array($requestLanguage, $languages) ? $requestLanguage : $defaultLanguage;

    PLL()->curlang = PLL()->model->get_language($language);
  }
});

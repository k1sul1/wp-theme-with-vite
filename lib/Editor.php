<?php

namespace k1;

function getDefaultBlockRenderSettings() {
  return [
    'x' => 'y'
  ];
}

function hyphenate($str = '') {
  return apply_filters('hyphenate', $str);
}

/**
 * Turns links into non-functional ones. Used in render previews.
 */
function neutralizeLink($link, $isPreview = false) {
  return $isPreview ? '#' : $link;
}



/**
 * Tweaks to the classic editor. Only usable inside ACF fields.
 */
add_filter("tiny_mce_before_init", function ($init) {
  $style_formats = array(
    [
      "title" => "Read more link",
      "classes" => "k1-button readmore",
      "block" => "a",
    ],
    [
      "title" => "Filled button",
      "classes" => "k1-button fill",
      "block" => "a",
    ],
    [
      "title" => "Alternative filled button",
      "classes" => "k1-button alt-fill",
      "block" => "a",
    ],
    [
      "title" => "Hollow button",
      "classes" => "k1-button hollow",
      "block" => "a",
    ],

    [
      "title" => "Highlight",
      "classes" => "highlight",
      "inline" => "span",
    ],

    [
      "title" => "Much bigger",
      "classes" => "much-bigger",
      "inline" => "span",
    ],

    [
      "title" => "Bigger",
      "classes" => "bigger",
      "inline" => "span",
    ],

    [
      "title" => "Smaller",
      "classes" => "smaller",
      "inline" => "span",
    ],

    [
      "title" => "Much smaller",
      "classes" => "much-smaller",
      "inline" => "span",
    ]);

  $init["style_formats"] = json_encode($style_formats);

  return $init;
});

add_filter("mce_buttons_2", function ($buttons) {
  array_unshift($buttons, "styleselect");
  return $buttons;
});

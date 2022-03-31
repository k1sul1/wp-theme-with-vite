<?php

namespace k1;

function librarySvg(int $mediaId, array $data = []) {
  $data = \k1\params([
    'className' => ['k1-svg'],
  ], $data);

  $wrapper = function($svgEl) use ($data) {
    $class = \k1\className(...$data['className']);

    return "<div $class>$svgEl</div>";
  };

  $src = get_post_meta($mediaId, '_wp_attached_file', true);
  $uploadDir = wp_get_upload_dir();

  $fullSrc = $uploadDir['basedir'] . '/' . $src;

  return $wrapper(file_get_contents(
    $fullSrc
  ));
}

function buildStyleString($styles = []) {
  $str = '';

  foreach ($styles as $k => $v) {
    $str .= "$k: $v;";
  }

  return \esc_attr($str);
}

/**
 * This is automatically updated to ACF
 */
function getPostListTemplateList() {
  $templates = [
    'SimplePostListItem' => '\k1\Templates\SimplePostListItem',
    'NotSimplePostListItem' => '\k1\Templates\SimplePostListItem',
  ];

  return $templates;
}

<?php
/*
 * pre_get_posts and others here
 */
namespace k1\QueryModifiers;

add_action('init', function() {
  $tag = get_taxonomy('post_tag');
  $cat = get_taxonomy('category');

  add_action('pre_get_posts', function($query) use ($cat, $tag) {
    // $query is passed by reference

    if ($query->is_main_query()) {
      // The custom post types use the default tags and cats, but they aren't shown in tag and cat archives.
      // This changes that.

      if ($query->is_tag()) {
        $query->set('post_type', $tag->object_type);
      }

      if ($query->is_category()) {
        $query->set('post_type', $cat->object_type);
      }
    }

    return $query;
  });
});

<?php
namespace k1\Templates;

/**
 * Very simple post list template
 */
function SimplePostListItem($data = [], $i, $isPreview = false) {
  $id = get_the_ID();
  $data = \k1\params([
    'title' => \k1\title(),
    'link' => \get_permalink(),
    'categories' => get_the_category(),
    // 'fields' => \get_fields($id), // all acf fields
  ], $data);
  $catCount = count($data["categories"]);

  $data["link"] = \k1\neutralizeLink($data["link"], $isPreview);
  ?>

  <article class="k1-simplepostlistitem">
    <a href="<?=\esc_attr($data['link'])?>" class="link">
      <h3><?=\esc_html($data['title'])?></h3>
    </a>

    <?php foreach ($data["categories"] as $i => $cat) {
      $link = get_term_link($cat);
      $name = $cat->name;

      echo "<a href='$link' class='category-link'>$name</a>";

      if ($catCount - $i > 1) {
        echo " | ";
      }
    } ?>
  </article><?php
}

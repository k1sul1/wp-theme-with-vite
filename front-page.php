<?php
namespace k1;

/**
 * This assumes a static front page.
 */

get_header(); ?>

<div class="k1-root k1-root--front-page">
  <?php

  while (have_posts()) { the_post();
    gutenbergContent();
  } ?>
</div>

<?php get_footer();

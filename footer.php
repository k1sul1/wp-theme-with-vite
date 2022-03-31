<?php
/**
 * The footer template. Included by get_footer().
 */
namespace k1;

$app = app();
$footer = $app->getOption("footer");

$footer['menu'] = empty($footer['menu']) ? [] : $footer['menu'];
$footer['copyrightText'] = empty($footer['copyrightText']) ? '&copy;' : $footer['copyrightText'];
$footer['tagline'] = empty($footer['tagline']) ? 'Something about making the world a better place.' : $footer['tagline'];

?>

  </main>
  <footer class="site-footer">
    <div class="k1-container">
      <ul>
        <?php foreach ($footer["menu"] as $item) {
          $p = get_post($item);
          $link = get_permalink($p);
          $title = get_the_title($p); ?>

          <li><a href="<?=$link?>"><?=$title?></a></li><?php
        } ?>
      </ul>
      <p class="copyright"><?=$footer["copyrightText"]?></p>
      <p class="tagline"><?=$footer["tagline"]?></p>
    </div>
  </footer>
  <?php wp_footer(); ?>
  </body>
</html>

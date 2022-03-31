<?php get_header();

$app = \k1\app();
?>

<div class="k1-root--404">
  <article class="k1-gutenberg">
    <div class="k1-container">
      <h1><?=$app->i18n->getText('Title: 404')?></h1>
    </div>
  </article>
</div>

<?php get_footer(); ?>

<?php
/**
 * The index page. The last file WordPress will try to load when resolving template.
 * See http://wphierarchy.com for help.
 */
namespace k1;

global $wp_query;

// is_post_type_archive('post') does not work!
$isBlogArchive = isset($wp_query) && (bool) $wp_query->is_posts_page;
$obj = \get_queried_object();
$title = \is_archive() ? get_the_archive_title() : $obj->post_title;

$app = app();
// $postlisting = $app->getBlock('PostListing');

get_header(); ?>

<div class="k1-root k1-root--archive">
    <h5><strong><?=$app->i18n->getText('Title: Blog Subheading')?></strong></h5>
    <h2><strong><?=$app->i18n->getText('Title: Blog Heading')?></strong></h2>
    </div>

    <div class="react-example"></div>
</div>

<?php get_footer();

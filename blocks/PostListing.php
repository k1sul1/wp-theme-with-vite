<?php
namespace k1\Blocks;

/**
 * Gutenberg blog for queurying and displaying posts.
 *
 * The client controls are (to be) implemented with React.
 */
class PostListing extends \k1\Block {
  public function getSettings() {
    $parent = parent::getSettings();

    return \k1\params($parent, [
      'category' => 'widgets',
    ]);
  }

  public function render($fields, $isPreview = false) {
    $data = \k1\params(
      array_merge(
        \k1\getDefaultBlockRenderSettings(), [
          'mode' => 'automatic', // automatic | idList | mainQuery
          // There are no query options when you use mainQuery.
          // mainQuery is not available as an option in the UI,
          // but it's useful when using this block from the code directly.

          // You can't use arrays in nested parameters or they get merged,
          // instead of overwriting. This code definition is meant to serve the
          // developers, so you don't have to constantly keep looking into
          // your ACF definitions.

          // Simple values such as strings, booleans and numbers work as default values here. Provide defaults for arrays a bit later in the code,
          // if you need them.
          'automatic' => [
            'amount' => 3,
            'postTypes' => [], // ['post']
            'category' => false, // [1, 2, 3]
            'tag' => false, // [4, 5, 6]
          ],

          'idList' => [
            'list' => [], // [1, 2, 3]
          ],

          // Self-explanatory
          'paginated' => false,

          // If there are multiple PostListing blocks on the same page,
          // only one of them can have this set as true, or all of them are affected.
          'trackStateInUrl' => true,

          // Template key from the template field.
          // Needs to exist in getPostListTemplate().
          'template' => 'SimplePostListItem',
        ]
      ),
      $fields);

    $classes = array_merge(
      [
        "k1-block",
        "k1-postlisting",
      ],
    );
    $page = (int) get_query_var("paged");
    $templates = \k1\getPostListTemplateList();

    switch ($data["mode"]) {
      case "automatic":
      $query = new \WP_Query([
        'post_type' => $data["automatic"]["postTypes"],
        "posts_per_page" => $data["automatic"]["amount"],
        "category__in" => $data["automatic"]["category"] ?? false,
        "tag__in" => $data["automatic"]["tag"] ?? false,
        "ignore_sticky_posts" => true,
        'post__not_in' => [get_the_ID()],
        'paged' => $page,
      ]);

      break;

      case "specific":
        $query = new \WP_Query([
          'post_type' => 'any',
          'post__in' => ($data["idList"]["list"]),
          "posts_per_page" => !empty($data["idList"]["list"]) ? count($data["idList"]["list"]) : 10,
          'orderby' => 'post__in',
          "ignore_sticky_posts" => true,
          'paged' => $page,
        ]);
      break;

      case "mainQuery":
        // FYI; This will cause an infinite loop if you're using it in the
        // wrong place. That's why it's not available as an UI option.

        $query = null;
      break;

      default:
        throw new \Exception("Invalid mode $data[mode]");
    }

    ?>

    <div <?=\k1\className(...$classes)?> data-template="<?=($data['template'])?>">
      <?php $this->PostList([
        'query' => $query,
        'template' => $templates[$data['template']],
        'paginated' => $data["paginated"],
        'trackStateInUrl' => $data["trackStateInUrl"],
        'taxTermFilters' => $data["taxTermFilters"],
      ], $isPreview); ?>
    </div><?php
  }

  /**
   * While have posts loop that displays posts with the provided template.
   * Uses the main query if no query is provided.
   */
  public function PostList($data = [], $isPreview = false) {
    $data = \k1\params([
      'query' => null,
      // 'template' => '\k1\Templates\SimplePostListItem',
      'template' => null,
      'paginated' => false,
      'trackStateInUrl' => true,
      'taxTermFilters' => [], // ['post_tag']
    ], $data);
    $app = \k1\app();
    $linkId = uniqid();

    if (is_null($data["query"])) {
      global $wp_query;
      $havePosts = "have_posts";
      $thePost = "the_post";

      $pages = $wp_query->max_num_pages;
      $queryParams = $wp_query->query;
      $queryParams['posts_per_page'] = (int) get_option('posts_per_page');

      // For some very stupid reason, the MAIN query returns this in a string format. Custom queries return this as int.
      // That kills the code. This fixes the code.
      $queryParams["paged"] = !empty($queryParams["paged"]) ? (int) $queryParams["paged"] : 0;
    } else {
      $havePosts = [$data["query"], "have_posts"];
      $thePost = [$data["query"], "the_post"];
      $pages = $data["query"]->max_num_pages;
      $queryParams = $data["query"]->query;
    }

    $paginateProps = [
      'total' => $pages,
      'prev_text' => $app->i18n->getText('Pagination: Previous'),
      'next_text' => $app->i18n->getText('Pagination: Next'),
    ];

    $templateName = explode('\\', $data['template']);
    $templateName = end($templateName);

    $filterClasses = ['k1-postlisting__filters', 'k1-container'];
    $listClasses = ['k1-postlisting__list', 'k1-container', 'template-' . strtolower($templateName)];
    $paginationClasses = ['k1-postlisting__pagination', 'k1-container'];

    if (!empty($data["taxTermFilters"])) {
      $taxonomies = $data["taxTermFilters"];
      $taxTerms = [];

      foreach ($taxonomies as $tax) {
        $taxTerms[] = array_map(function($term) {
          $term->active = false;

          return $term;
        }, get_terms([
          'taxonomy' => $tax,
          'hide_empty' => true,
        ]));
      }

      // The invidual terms have the taxonomy name included in them, so the resulting arrays can be just merged into one.
      $taxTerms = array_merge(...$taxTerms)
      ?>
      <div <?=\k1\className(...$filterClasses)?> data-link="<?=esc_attr($linkId)?>" data-taxterms='<?=(wp_json_encode($taxTerms))?>'></div>
      <?php
    }
    ?>

    <!-- Progressive enhancement! -->
    <div class="react-root"></div>

    <div
      <?=\k1\className(...$listClasses)?>
      data-pagesize="<?=esc_attr($queryParams["posts_per_page"])?>" <?php // Used in CSS ?>
      data-trackstateinurl="<?=($data["trackStateInUrl"]) ? 'true' : 'false'?>"
      data-link="<?=esc_attr($linkId)?>"
      data-template="<?=($templateName)?>"
      data-query='<?=(wp_json_encode($queryParams))?>'>

      <?php
      // This div will have it's contents innerHTML'd when the query changes.
      // The query is changed by the React components.
      $i = 0;

      if (!$havePosts()) {
        $title = \k1\app()->i18n->getText('No posts found');

        echo "<h2>$title</h2>";
      }

      while ($havePosts()) { $thePost(); $i++;
        // The templates should use the global $post object for their data
        // ie. the_title, the_content(), etc.

        // Only generic parameters can be passed into the template,
        // as the params are shared between all templates
        $data['template']([], $i, $isPreview);
      } ?>
    </div><?php

    if ($data["paginated"]) { ?>
      <div
        <?=\k1\className(...$paginationClasses)?>
        data-link="<?=esc_attr($linkId)?>"
        data-total='<?=(esc_attr($paginateProps["total"]))?>'
      >
        <?=\paginate_links($paginateProps)?>
      </div><?php
    } else { ?>
      <div class="pagination-placeholder">
        <!-- Pagination would be here if it was enabled.
        This ensures consistency with flexbox layouts. -->
      </div>
      <?php
    }

    // the_post messes with the global post object, reset it or the rest of the page breaks
    wp_reset_query();
    wp_reset_postdata();
  }
}

<?php

namespace k1\Routes;

use Error;

/**
 * A REST endpoint for the PostListing block. Ignores all rules of REST and just returns a bunch of JSON that contains HTML. That HTML is then .innerHTML'd and transitions are run.
 *
 * It was done this way as otherwise all of the PostListing templates would've had to be done twice, in PHP and in React.
 *
 * If you're worried about XSS, don't be. This is content created by authenticated users, and those
 * can put any JS on any page they'd like using the WYSIWYG editor. It's a feature!
 */
class PostListing extends \k1\RestRoute {
  public function __construct() {
    parent::__construct('k1/v1', 'postlisting');

    $this->registerEndpoint(
      '/query',
      [
        'methods' => 'GET',
        'callback' => [$this, 'getHTMLChunk'],
        'permission_callback' => '__return_true',
      ],
      [
        'expires' => 1,
      ]
    );
  }

  /**
   * I can reinvent the wheel or I can use a plugin that does it better anyway.
   *
   * I chose the same wheel.
   */
  public function sanitizeArgs($args) {
    if (!function_exists('wp_query_route_to_rest_api_get_instance')) {
      error_log("Missing dependency aucor/wp_query-route-to-rest-api, QUERIES ARE NOT PROTECTED!");

      return $args;
    }

    $externalHelp = \wp_query_route_to_rest_api_get_instance();
    return $externalHelp->sanitize_query_parameters($args);
  }

  /**
   * Get a chunk of HTML based on the parameters of the request. Used for pagination and filtering of posts in a PostListing block.
   */
  public function getHTMLChunk($request) {
    $params = $request->get_params();
    $templates = \k1\getPostListTemplateList();

    $template = $params['template'] ?? null;
    $args = $this->sanitizeArgs(json_decode($params['args'] ?? null));

    if (!($templates[$template] ?? null)) {
      return new \WP_REST_Response([
        'error' => "Template $template does not exist",
      ], 500);
    }

    $template = $templates[$template];
    $query = new \WP_Query($args);

    $havePosts = [$query, "have_posts"];
    $thePost = [$query, "the_post"];
    $pages = $query->max_num_pages;

    \ob_start();

    $i = 0;
    if (!$havePosts()) {
      $title = \k1\app()->i18n->getText('No posts found');

      echo "<h2>$title</h2>";
    }

    while ($havePosts()) { $thePost(); $i++;

      // The templates are to fetch their own stuff using the just set global $post object,
      // nothing but generic parameters can be used here, as they are shared between all templates.
      $template([], $i, false);
    }
    $html = \ob_get_clean();

    return ['html' => $html, 'args' => $args, 'pages' => $pages];
  }
}

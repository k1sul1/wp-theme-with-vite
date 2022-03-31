<?php
namespace k1\Blocks;

/**
 * The ol' swiss army knife. This block is responsible for displaying a post listing block.
 * It uses the PostList template and wraps some controls around it. The controls are implemented with React.
 *
 * This is bit of a mess simply due to the fact that it's used almost everywhere.
 */
class Example extends \k1\Block {
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
          'x' => 'not Y',
          'data' => '',
        ]
      ),
      $fields);

    // Note that 'x' isn't getting sent to the frontend here.
    ?>

    <div data-data="<?=($data['data'])?>">
      <div class="react-example"></div>

      <p>Neat!</p>
    </div><?php
  }
}

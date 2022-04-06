# Pretty much blank WordPress theme base, built w/ Vite

Vite is a webpack alternative. It doesn't have all the features but it's faster and nicer to use.

https://vitejs.dev/

This theme is "loosely" based on [wordpress-theme-base](https://github.com/k1sul1/wordpress-theme-base).

## Requirements

- PHP 7.4 or higher
- NodeJS, use the current LTS for optimal results.
- ACF Pro, for Gutenberg blocks w/ ACF
- https://github.com/aucor/wp_query-route-to-rest-api, for PostListing block
- [k1sul1/k1kit](https://github.com/k1sul1/k1kit), for sharing code between projects

## Getting started

Clone the repo into your theme directory, `npm install` and `npm run dev`.

It should just work. This is what it should look like after you've added the two example components on the Sample Page, assuming a fresh installation.

![screenshot](screenshot.jpeg)

It's very barebones. Very. Add in whatever you want, nothing should get in the way.

However, if you end up using this repository as a template for your own work, **read the documentation of k1kit**, esp. about the parts that you might not want.

In reality, I'd suggest that you just take the relevant parts from these files:

- classes/class.vite.php
- functions.php
- vite.config.ts

The header contents are crucial for Vite to work properly.

_If you're insane enough to use this and k1kit in production, at least lock the version of k1kit into whatever version is the latest release. I might update it any time, and I will not be responsible for breaking your site._

_You have been warned._

## Features

Everything from [k1sul1/k1kit](https://github.com/k1sul1/k1kit) and a few cherries on top. See it for more detailed description.

- Custom Gutenberg block toolkit.
- Multilinguality support using Polylang, falling back to Core
- Reusable & combinable data-driven templates
  - Yes, they can get ugly. So is PHP.
- Asset filename hashing for cache busting
- Hot module reloading (HMR) for CSS & _compatible_ JS
- React support
- CSS preprocessor support
  - I prefer Stylus, if you want to use SCSS, that's easy. Install `node-sass` with npm.
- `<title>` is prefixed with the current environment to avoid confusion when working with multiple instances
- Namespaces (believe it or not, these are rare in the WordPress world)

## Issues?

Always.

## Vite dev server keeps refreshing my page!

AFAIK the dev server client refreshes the page when it can't use HMR. If you find that annoying, like I do, use `npm run dev:nohmr`.

There doesn't seem to be working workarounds for this issue and Vite lacks a config option to disable forced reloads.

Throwing in vite:beforeFullReload as showcased in this issue doesn't work for me.
https://github.com/vitejs/vite/issues/6695

## Self-Help section

### Block creation

Create a file to blocks/. Name it whatever you want, just capitalize the first letter.

```php
<?php
// blocks/Example.php
namespace k1\Blocks;

class Example extends \k1\Block {
  public function render($data = []) { ?>
    <div class="example-block">
      <?=get_field('example')?>
    </div>
  <?php
  }

  /*
   * If you need to change the settings, that's easy.
   * If you don't, don't define this function.
   */
  public function getSettings() {
    $data = parent::getSettings();
    $data['mode'] = 'preview';

    return $data;
  }
}
```

Then just add a new field group, like you normally would. Just select your block as the location.

### Can I call the custom blocks manually?

Yes. This example showcases how you can also cache those manually called blocks.

```php
<?php
namespace k1;

$app = app();
$hero = $app->getBlock('Hero');

echo withTransient(capture([$hero, 'render'], [
  'content' => [
    'data' => '<h1>' . title($title) . '</h1>',
    'position' => 'centerBottom',
  ],
  'background' => [
    'backgroundMedia' => [
      'type' => 'image',
      'image' => [
        'data' => $thumb,
        'imagePosition' => 'centerCenter'
      ]
    ]
  ]
]), [
  'key' => 'indexHero',
  'options' => [
    'type' => 'manual-block',
    'expiry' => \HOUR_IN_SECONDS,
  ]
], $missReason);

echo "\n\n\n<!-- Block " . $hero->getName() . " cache: " . transientResult($missReason) . " -->";
```

When calling manually, you have to make sure that you use the same datastructure as ACF.

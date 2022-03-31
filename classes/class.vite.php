<?php

namespace k1;

class ViteAsset {
  public $file = null;
  public $src = null;

  public $isEntry = false;
  public $isDynamicEntry = false;

  public $dynamicImports = null;
  public $imports = null;

  public $css = null;
  public $assets = null;

  public function __construct($rawAsset) {
    $this->file = $rawAsset->file;

    $this->src = $rawAsset->src ?? null;
    $this->isEntry = $rawAsset->isEntry ?? false;
    $this->isDynamicEntry = $rawAsset->isDynamicEntry ?? false;

    $this->dynamicImports = $rawAsset->dynamicImports ?? null;
    $this->imports = $rawAsset->imports ?? null;

    $this->css = $rawAsset->css ?? null;
    $this->assets = $rawAsset->assets ?? null;
  }
}

class Vite {
  public $manifest = [];

  // handle prefix
  public $hp = "k1";

  public $buildDir = "dist";
  public $serverUrl = "http://localhost:8888";
  public $serverPort = 8888;

  protected $dev = false;

  public function __construct(string $path) {
    $this->manifest = (array) json_decode(file_get_contents($path));

    if ($this->devServerExists()) {
      $this->dev = true;
    }
  }

  public function isDev() {
    return $this->dev;
  }

  public function isCSS(string $assetName) {
    return strpos($assetName, '.css') !== false;
  }

  public function isJS(string $assetName) {
    $js = strpos($assetName, '.js') !== false;
    $ts = strpos($assetName, '.js') !== false;

    if (strpos($assetName, '.js') !== false) {
      return true;
    } else if (strpos($assetName, '.ts') !== false) {
      return true;
    } else {
      return false;
    }
  }

  public function enqueueJS(string $filename, $dependencies = [], $inFooter = true) {
    $handle = basename($filename);
    $handle = "{$this->hp}-$handle";

    wp_enqueue_script(
      $handle,
      $filename,
      $dependencies,
      null,
      $inFooter
    );

    add_filter('script_loader_tag', function($tag, $h, $src) use ($handle) {
      if ($h === 'vite/client-js') {
        // continue as normal
      } else if ($handle !== $h) {
        return $tag;
      }

      $tag = '<script id="' . esc_attr($handle) . '" type="module" src="' . esc_url($src) . '"></script>';
      return $tag;
    } , 10, 3);

    return $handle;
  }

  public function enqueueCSS(string $filename, $dependencies = []) {
    $handle = basename($filename);
    $handle = "{$this->hp}-$handle";

    wp_enqueue_style(
      $handle,
      $filename,
      $dependencies,
      null,
    );

    return $handle;
  }

  public function enqueue(string $assetName, $dependencies = [], $options = []) {
    $isJS = $this->isJS($assetName);
    $asset = $this->getAsset($assetName);

    if (!$asset) {
      $message = "Unable to enqueue asset $assetName. It wasn't present in the {$this->name} manifest. ";

      throw new \Exception($message);
    }

    $filename = $this->getAssetFilename($asset);
    $handles = [];

    if ($isJS) {
      $handles[] = $this->enqueueJS($filename, $dependencies, true);
    }

    if ($asset->css && !$this->isDev()) {
      $files = $asset->css;

      foreach ($files as $i => $css) {
        $cssFile = $this->withBuildDirectory($css);
        $handles[] = $this->enqueueCSS($cssFile);
      }
    }

    return $handles;
  }

  public function getAsset(string $assetName) {
    $raw = $this->manifest[$assetName];


    return !empty($raw) ? new ViteAsset($raw) : false;
  }

  public function getAssetFilename(ViteAsset $asset, $forBrowser = true) {
    // $asset = $this->getAsset($assetName);
    if (!$this->isDev()) {
      $filename = $asset->file;

      $filename = $this->withBuildDirectory($filename, $forBrowser);
    } else {
      // $filename = $this->serverUrl . '/' . $assetName;
      $filename = $this->serverUrl . '/' . $asset->src;
    }

    return $filename;
  }

  /**
   * The asset manifest doesn't know of WP, it assumes the files are available in webroot.
   */
  public function withBuildDirectory(string $filename, $forBrowser = true) {
    return ($forBrowser ? \get_stylesheet_directory_uri() : \get_stylesheet_directory()) . "/{$this->buildDir}/$filename";
  }

  /**
   * This is the best way I found to check for the existence.
   *
   * fopen throws warnings if it can't find whatever it's looking for. I suppressed those with STFU operator and cleared the error.
   *
   * If I don't do that, you get to enjoy a php-error class in wp-admin which adds whitespace. Don't ask me how long it took me to figure that out.
   */
  private function devServerExists(){
    if (\k1\isProd()) {
      return false;
    }

    // This should work on Mac & Linux. It does not work from the client!
    // If you're not using Docker, first, what is wrong with you? Second, I've got you covered.
    $file = @fopen("http://host.docker.internal:{$this->serverPort}/@vite/client", "r");

    if (!$file) {
      error_clear_last();

      // Not running on Docker? This should work.
      $file = @fopen($this->serverUrl . "/@vite/client", "r");

      if (!$file) {
        error_clear_last();

        return false;
      }
    }

    fclose($file);
    return true;
  }
}

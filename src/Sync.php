<?php

namespace HC\CustomPostTypeUiSync;

use HC\CustomPostTypeUiSync\Traits\SingletonTrait;

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

/**
 * The core plugin class.
 */
final class Sync
{
  use SingletonTrait;

  /**
   * Path to store JSON files in.
   */
  private $path;

  /**
   * Define the core functionality of the plugin.
   */
  public function __construct()
  {
    $this->path = apply_filters('hccptuis_path', get_stylesheet_directory() . "/cptui-json");
    $this->dependencies();
    $this->register_hooks();
  }

  /**
   * Import dependencies.
   */
  public function dependencies()
  {
    include_once(WP_PLUGIN_DIR . "/custom-post-type-ui/inc/utility.php");
    include_once(WP_PLUGIN_DIR . "/custom-post-type-ui/inc/tools.php");
  }

  /**
   * Register hooks.
   */
  public function register_hooks()
  {
    add_action("cptui_after_update_post_type", array($this, "export"), 10, 2);
    add_action("cptui_after_delete_post_type", array($this, "export"), 10, 2);
    add_action("cptui_after_update_taxonomy", array($this, "export"), 10, 2);
    add_action("cptui_after_delete_taxonomy", array($this, "export"), 10, 2);
    add_action("wp_loaded", array($this, "import"), 10, 2);
  }

  /**
   * Scan JSON folder for files to be imported.
   */
  public function scan()
  {
    $json_files = array();
    if (!is_dir($this->path)) {
      return $json_files;
    }
    
    $files = scandir($this->path);

    if ($files) {
      foreach ($files as $filename) {
        // Ignore hidden files.
        if ($filename[0] === '.') {
          continue;
        }

        // Ignore sub directories.
        $file = untrailingslashit($this->path) . '/' . $filename;
        if (is_dir($file)) {
          continue;
        }

        // Ignore non JSON files.
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if ($ext !== 'json') {
          continue;
        }

        if (!is_readable($file)) {
          die("Cannot read Custom Post Type UI JSON file.");
        }

        // Read JSON data.
        $json = json_decode(file_get_contents($file), true);
        if (!is_array($json) || !$json['type']) {
          continue;
        }

        // Append data.
        $json_files[$json['type']] = $file;
      }
    }

    return $json_files;
  }

  /**
   * Save JSON file.
   */
  public function save($file, $data)
  {
    $d = apply_filters('hccptuis_before_save', $data);
    $result = file_put_contents($file, wp_json_encode($d, JSON_PRETTY_PRINT));
    return is_int($result);
  }

  /** 
   * Create nonce
   */
  public function create_nonce()
  {
    $_REQUEST["cptui_typetaximport_nonce_field"] = wp_create_nonce("cptui_typetaximport_nonce_action");
  }

  /**
   * Load JSON file from local theme directory.
   */
  public function import()
  {
    $files = $this->scan();
    $this->create_nonce();
    $payload = array();

    foreach ($files as $type => $file) {
      $json = json_decode(file_get_contents($file), true);

      if (!empty($json) && isset($json) &&
        isset($json['data']) && $json['data'] &&
        isset($json['type']) && $json['type']) {
        $d = apply_filters('hccptuis_before_import', $json);
        $payload[$type] = $d['data'];
      }
    }

    cptui_import_types_taxes_settings($payload);
  }

  /**
   * Remove JSON file.
   */
  public function remove($form_id)
  {
    $files = $this->scan();

    foreach ($files as $id => $file) {
      if ((int)$id === (int)$form_id) {
        unlink($file);
        return;
      }
    }
  }

  /**
   * Export JSON file to local theme directory.
   */
  public function export()
  {
    if (!is_writable($this->path)) {
      die("Cannot write Custom Post Type UI JSON file.");
    }

    $post_type_file = untrailingslashit($this->path) . '/cpt-post-types.json';
    $taxonomy_file = untrailingslashit($this->path) . '/cpt-taxonomies.json';

    $post_data = array(
      "type" => "cptui_post_import", 
      "data" => cptui_get_post_type_data()
    )
    ;
    $taxonomy_data = array(
      "type" => "cptui_tax_import",
      "data" => cptui_get_taxonomy_data()
    );

    $this->save($post_type_file, $post_data);
    $this->save($taxonomy_file, $taxonomy_data);
  }
}

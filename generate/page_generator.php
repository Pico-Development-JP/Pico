<?php 
define('ROOT_DIR', realpath(__DIR__ . '/../') . '/');
define('GENERATOR_DIR', realpath(__DIR__ . '/modules/') . '/');
define('LOG_DIR', realpath(__DIR__ . '/logs/') . '/');

$gen = new Page_Generator();
$gen->run();

class Page_Generator{

  private $config;

  public function __construct(){
    $this->config = $this->get_config();
  }

  public function run(){
    $modules = $this->get_files(GENERATOR_DIR, '.php');  
    $args = array($this->config);
    // Copy from pico.php(load_plugins(), run_hooks())
    if (!empty($modules)) {
      foreach ($modules as $module) {
        include_once($module);
        $module_name = preg_replace("/\\.[^.\\s]{3}$/", '', basename($module));
        if (class_exists($module_name)) {
          $obj = new $module_name;
          if (is_callable(array($obj, "run"))) {
            call_user_func_array(array($obj, "run"), $args);
          }
        }
      }
    }
  }

  // Copy from pico.php
  
  /**
   * Loads the config
   *
   * @return array $config an array of config values
   */
  protected function get_config()
  {

    $this->config = @include_once(ROOT_DIR . 'config.php');

    $defaults = array(
      'theme' => 'default',
      'date_format' => '%D %T',
      'twig_config' => array('cache' => false, 'autoescape' => false, 'debug' => false),
      'pages_order_by' => 'alpha',
      'pages_order' => 'asc',
      'excerpt_length' => 50,
      'content_dir' => 'content-sample/',
    );

    if (is_array($this->config)) {
      $this->config = array_merge($defaults, $this->config);
    } else {
      $this->config = $defaults;
    }

    return $this->config;
  }

  /**
   * Helper function to recusively get all files in a directory
   *
   * @param string $directory start directory
   * @param string $ext optional limit to file extensions
   * @return array the matched files
   */
  protected function get_files($directory, $ext = '')
  {
    $array_items = array();
    if ($handle = opendir($directory)) {
      while (false !== ($file = readdir($handle))) {
        if (in_array(substr($file, -1), array('~', '#'))) {
            continue;
        }
        if (preg_match("/^(^\.)/", $file) === 0) {
          if (is_dir($directory . "/" . $file)) {
            $array_items = array_merge($array_items, $this->get_files($directory . "/" . $file, $ext));
          } else {
            $file = $directory . "/" . $file;
            if (!$ext || strstr($file, $ext)) {
                $array_items[] = preg_replace("/\/\//si", "/", $file);
            }
          }
        }
      }
      closedir($handle);
    }

    return $array_items;
  }

}
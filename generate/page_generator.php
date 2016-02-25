<?php 
define('ROOT_DIR', realpath(__DIR__ . '/../') . '/');
define('GENERATOR_DIR', realpath(__DIR__ . '/modules/') . '/');
define('LOG_DIR', realpath(__DIR__ . '/logs/') . '/');
require_once(ROOT_DIR . "lib/Pico.php");

if(count($argv) > 1){
  $names = array_slice($argv, 1);
}else{
  $names = false;
}

class Page_Generator extends Pico {

  public function run($modulenames = null){
    $modules = $this->getFiles(GENERATOR_DIR, '.php');
    $this->loadConfig();
    
    $args = array($this->getConfig());
    // Copy from pico.php(load_plugins(), run_hooks())
    if (!empty($modules)) {
      foreach ($modules as $module) {
        if(strpos($module, "vendor") === false){
          include_once($module);
          $module_name = preg_replace("/\\.[^.\\s]{3}$/", '', basename($module));
          if (class_exists($module_name) && ($modulenames == false || in_array($module_name, $modulenames))) {
            echo sprintf("> %s", $module_name);
            $obj = new $module_name;
            if (is_callable(array($obj, "run"))) {
              echo " -> run()";
              echo "\n";
              call_user_func_array(array($obj, "run"), $args);
            }else{
              echo " method not found skipped";
              echo "\n";
            }
            echo "\n";
          }
        }
      }
    }
  }

}

// instance Pico
$gen = new Page_Generator(
    ROOT_DIR,    // root dir
    ROOT_DIR . 'config/',  // config dir
    ROOT_DIR . 'plugins/', // plugins dir
    ROOT_DIR . 'themes/'   // themes dir
);
$gen->run($names);

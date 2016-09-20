<?php
require_once "update_util.php";

class Update extends Update_Util{

  public function precheck(){
    chdir(ROOT_DIR);
    $this->loadConfig();

    $success = FALSE;
    $msg = "Module Not Found";
    $args = array($this->getConfig());
    $p = $_SERVER['QUERY_STRING'];
    $found = FALSE;
    if($p){
      $modules = $this->getFiles(UPDATER_DIR, '.php');
      parse_str($p, $request);
      $module = "";
      // 検索
      foreach ($modules as $module) {
        if(strpos($module, "vendor") === false){
          include_once($module);
          $module_name = preg_replace("/\\.[^.\\s]{3}$/", '', basename($module));
          if (class_exists($module_name) && ($module_name == $request["name"])) {
            $obj = new $module_name($this);
            if (is_callable(array($obj, "precheck")) &&
              is_callable(array($obj, "run"))) {
              $msg = call_user_func_array(array($obj, "precheck"), $args);
              $found = $module; 
            }
          }
        }
      }
      
      if($found){
        $cmd = sprintf("nohup php %s/update_run.php %s", __DIR__, $found);
        // 実行
        header("HTTP/1.1 202 Accepted");
        if (PHP_OS !== 'WIN32' && PHP_OS !== 'WINNT') {
          exec($cmd . ' >/dev/null 2>&1 &');
        } else {
          $fp = popen('start ' . $cmd, 'r');
          pclose($fp);
        }

        if(is_null($msg)){
          // 何も出力しない。なにか文字を返すと不正アプリとみなすWebフック対策
        }elseif($msg){
          $this->sendWebhook($msg, "Update Accepted");
          echo "$msg";
        }else{
          echo "<h1>Update Accepted</h1>\n";
        }
      }else{
        $msg = "Module Not Found";
        echo "<h1>$msg</h1>\n";
        $this->sendWebhook($msg, "Update Failed");
      }
      return $msg;
    }
  }

}

// instance Pico
$update = new Update(
    ROOT_DIR,    // root dir
    ROOT_DIR . 'config/',  // config dir
    ROOT_DIR . 'plugins/', // plugins dir
    ROOT_DIR . 'themes/'   // themes dir
);
$update->precheck();

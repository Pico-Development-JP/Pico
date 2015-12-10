<?php
require_once "update_util.php";

$update = new Update();
$update->precheck();

class Update{

  public function precheck(){
    chdir(ROOT_DIR);
    $util = Update_Util::getInstance();
    $success = FALSE;
    $msg = "Module Not Found";
    $args = array($util->config);
    $p = $_SERVER['QUERY_STRING'];
    $found = FALSE;
    if($p){
      $modules = $util->get_files(UPDATER_DIR, '.php', 1);
      parse_str($p, $request);
      $module = "";
      // 検索
      foreach ($modules as $module) {
        include_once($module);
        $module_name = preg_replace("/\\.[^.\\s]{3}$/", '', basename($module));
        if (class_exists($module_name) && ($module_name == $request["name"])) {
          $obj = new $module_name;
          if (is_callable(array($obj, "precheck")) &&
            is_callable(array($obj, "run"))) {
            $msg = call_user_func_array(array($obj, "precheck"), $args);
            $found = $module; 
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

        if($msg){
          $util->sendWebhook($msg, "Update Accepted");
          echo "$msg";
        }else{
          echo "<h1>Update Accepted</h1>\n";
        }
      }else{
        $msg = "Module Not Found";
        echo "<h1>$msg</h1>\n";
        $util->sendWebhook($msg, "Update Failed");
      }
      return $msg;
    }
  }

}

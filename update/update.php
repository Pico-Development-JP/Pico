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
    if($p){
      $modules = $util->get_files(UPDATER_DIR, '.php');
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
          }
        }
      }
      $cmd = sprintf("nohup php %s/update_run.php %s", __DIR__, $module);
      // 実行
      header("HTTP/1.1 202 Accepted");
      if (PHP_OS !== 'WIN32' && PHP_OS !== 'WINNT') {
        exec($cmd . ' >/dev/null 2>&1 &');
      } else {
        $fp = popen('start ' . $cmd, 'r');
        pclose($fp);
      }

      exec($cmd);
      if($msg){
        $util->sendWebhook($msg, "Update Accepted");
      }
      return $msg;
    }
  }

}

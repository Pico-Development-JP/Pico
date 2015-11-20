<?php
require_once "update_util.php";

if(!empty($argv[1]) && file_exists($argv[1])){
  $update = new Update_Run();
  $update->run($argv[1]);
}

class Update_Run{

  public function run(string $file){
    chdir(ROOT_DIR);
    $util = Update_Util::getInstance();
    include_once($file);
    $module_name = preg_replace("/\\.[^.\\s]{3}$/", '', basename($file));
    $args = array($util->config);
    $obj = new $module_name;
    if (is_callable(array($obj, "run"))) {
      $ret = call_user_func_array(array($obj, "run"), $args);
      $success = $ret["success"];
      $msg = $ret["message"];
    }
    if($success){
      $title = "Update Success";
      $icon = ":grinning:";
    }else{
      $title = "Update Failed";
      $icon = ":confounded:";
    }
    var_dump($msg);
    $util->sendWebhook($msg, $title, $icon);
    echo "<h1>$title</h1>\n";
    echo "<div>$msg</div>\n";
  }

}

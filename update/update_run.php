<?php
require_once "update_util.php";

if(!empty($argv[1]) && file_exists($argv[1])){
  $update = new Update_Run();
  $update->run($argv[1]);
}

class Update_Run{

  public function run($file){
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
    if(is_callable(array($obj, "get_update_files"))){
      $ret = call_user_func_array(array($obj, "get_update_files"), array());
      $flist = array();
      if($ret){
        foreach($ret as $entry){
          list($path, $is_create) = $entry;
          // TODO: ファイル処理用コマンドをここに入れる
          array_push($flist, ($is_create ? "+" : "-") . " " . str_replace(ROOT_DIR, "", $path) );
        }
        $msg .= "\n--File List--\n" . implode($flist, "\n");
      }
    }
    if($success){
      $title = "Update Success";
      $icon = ":grinning:";
    }else{
      $title = "Update Failed";
      $icon = ":confounded:";
    }
    $util->sendWebhook($msg, $title, $icon);
  }

}

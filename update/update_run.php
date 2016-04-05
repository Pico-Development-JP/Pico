<?php
require_once "update_util.php";

class Update_Run extends Update_Util{

  public function run($file = null){
    chdir(ROOT_DIR);
    $this->loadConfig();
    if(file_exists($file)){
      include_once($file);

      $module_name = preg_replace("/\\.[^.\\s]{3}$/", '', basename($file));
      $obj = new $module_name($this);
      if (is_callable(array($obj, "run"))) {
        $ret = call_user_func_array(array($obj, "run"), array());
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
      $this->sendWebhook($msg, $title, $icon);
    }
  }

}

if(!empty($argv[1]) && file_exists($argv[1])){
  $update = new Update_Run(
    ROOT_DIR,    // root dir
    ROOT_DIR . 'config/',  // config dir
    ROOT_DIR . 'plugins/', // plugins dir
    ROOT_DIR . 'themes/'   // themes dir
  );
  $update->run($argv[1]);
}

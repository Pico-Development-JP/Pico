<?php 
define('ROOT_DIR', realpath(__DIR__ . '/../') . '/');
define('UPDATER_DIR', realpath(__DIR__ . '/modules/') . '/');
define('LOG_DIR', realpath(__DIR__ . '/logs/') . '/');

$update = new Update();
$update->run();

class Update{

  private $config;

  public function __construct(){
    $this->config = $this->get_config();
  }

  public function run(){
    chdir(ROOT_DIR);
    $success = FALSE;
    $msg = "Module Not Found";
    $args = array($this->config);
    $p = $_SERVER['QUERY_STRING'];
    if($p){
      $modules = $this->get_files(UPDATER_DIR, '.php');  
      parse_str($p, $request);
      foreach ($modules as $module) {
        include_once($module);
        $module_name = preg_replace("/\\.[^.\\s]{3}$/", '', basename($module));
        if (class_exists($module_name) && ($module_name == $request["name"])) {
          $obj = new $module_name;
          if (is_callable(array($obj, "run"))) {
            $ret = call_user_func_array(array($obj, "run"), $args);
            $success = $ret["success"];
            $msg = $ret["message"];
          }
        }
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
    echo "<h1>$title</h1>\n";
    echo "<div>$msg</div>\n";
  }
  
  /**
   * Webhookに送信を行う内部関数
   *
   *  @param string $text ... 送信文
   *  @param string $name ... 送信者名
   *  @param string $icon ... アイコン
   */
  protected function sendWebhook($text, $name, $icon = ":email") {
    $hookaddr = $this->config["webhook"]["pull_notification"];
    if($hookaddr){
      $payload = array(
            "text" => $text,
            "username" => $name,
            "icon_emoji" => $icon,
          );
      // curl
      $curl = curl_init($hookaddr);
      try{
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array());
        curl_setopt($curl, CURLOPT_POSTFIELDS, array('payload' => json_encode($payload)));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $res = curl_exec($curl);
        $err = curl_error($curl);
        if($err) throw new Exception($err);
        if($res != "ok") throw new Exception($res);
      }catch(Exception $e){
        throw $e;
      }
      curl_close($curl);
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

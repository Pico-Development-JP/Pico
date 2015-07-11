<?php 
define('ROOT_DIR', realpath(__DIR__ . '/../') . '/');
define('LOG_DIR', realpath(__DIR__ . '/logs/') . '/');

$pull = new Pull();
$pull->run();

class Pull{

  private $config;

  public function __construct(){
    $this->config = $this->get_config();
  }

  public function run(){
    chdir(ROOT_DIR);
    exec('git pull 2>&1', $output, $ret);
    $out = implode("\n", $output);
    $title;
    $icon;
    if($ret == 0){
      $title = "Update Success";
      $icon = ":grinning:";
    }else{
      $title = "Update Failed";
      $icon = ":confounded:";
    }
    $this->sendWebhook($out, $title, $icon);
    echo "<h1>$title</h1>\n";
    echo "<div>$out</div>\n";
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
}

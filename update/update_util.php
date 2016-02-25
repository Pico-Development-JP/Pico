<?php
define('ROOT_DIR', realpath(__DIR__ . '/../') . '/');
define('UPDATER_DIR', realpath(__DIR__ . '/modules/') . '/');
define('LOG_DIR', realpath(__DIR__ . '/logs/') . '/');
require_once(ROOT_DIR . "lib/Pico.php");

class Update_Util extends Pico{

  /**
   * Webhookに送信を行う内部関数
   *
   *  @param string $text ... 送信文
   *  @param string $name ... 送信者名
   *  @param string $icon ... アイコン
   */
  public function sendWebhook($text, $name, $icon = ":email") {
    $hookaddr = $this->getConfig()["webhook"]["pull_notification"];
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
}

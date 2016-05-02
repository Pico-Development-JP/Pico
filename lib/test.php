<?php // @codingStandardsIgnoreFile

/*
  PicoプラグインをPHPUnitなどでテストする際に使用するPHPスクリプト。
  ユニットテストの際には以下のようにこのスクリプトをrequireしてからテストコードを書く。
  require_once(__DIR__."/../../lib/test.php");
  require_once("[テストしたいモジュール].php");

  このコードの中ではpicoという変数にテスト用のPicoオブジェクトが入っているので、
  GLOBALS配列より取り出して利用すると便利。
  
  public function setUp() {
    $this->pico = $GLOBALS['pico'];
    $this->test = new Pico_RemoveIndex($this->pico);
  }

*/
// load dependencies
$root = realpath(__DIR__."/../");
if (is_file($root.'/vendor/autoload.php')) {
  // composer root package
  require_once($root.'/vendor/autoload.php');
}
elseif(is_file($root.'/../../../vendor/autoload.php')) {
  // composer dependency package
  require_once($root.'/../../../vendor/autoload.php');
} else {
  die("Cannot find `vendor/autoload.php`. Run `composer install`.");
}

class PicoTest extends Pico {
  public function __construct($rootDir, $configDir, $pluginsDir, $themesDir) {
    parent::__construct($rootDir, $configDir, $pluginsDir, $themesDir);
    $this->loadConfig();
  }
}

// エラーが煩わしいので仮に。
$_SERVER['SERVER_PORT'] = 80;
$_SERVER['HTTP_HOST'] = "";

// instance Pico
$pico = new PicoTest(
    $root, // root dir
    'lib/', // config dir(テストにおいては外部のコンフィグファイルを読ませない)
    'plugins/', // plugins dir
    'themes/' // themes dir
);

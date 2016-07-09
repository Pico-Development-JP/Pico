<?php
if (isset($_SERVER['REQUEST_URI']) && !file_exists(__DIR__ . $_SERVER['REQUEST_URI'])) {
  $qs = $_SERVER['REQUEST_URI'];
  // 本来のクエリ文字列があれば追加
  if($qs[0] == "/") $qs = substr($_SERVER['REQUEST_URI'], 1);
  if(isset($_SERVER['QUERY_STRING'])){
    $qs = str_replace($_SERVER['QUERY_STRING'], "", $qs);
    $qs .= $_SERVER['QUERY_STRING'];
  }
  $qs = preg_replace("/.*\?/", "", $qs);
  // クエリ文字列として追加
  $_SERVER['QUERY_STRING'] = $qs;
}
return false;
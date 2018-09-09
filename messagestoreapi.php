<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
### Documentation
# $line should be arranged as:
# array(user_id , message , timestamp , message_type , message_id , array_of_cuts , displayname , imageurl);

define("CSV_STORE_FILE", "mesglist.csv");

## clean up the text
function sanitizemsg($input){
  #removed emoji because inscriber fails
  #$clean = preg_replace("/[^a-zA-Z\d\s\p{Thai}\x{2600}-\x{26FF}\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{E000}-\x{F8FF}\x{100001}-\x{10009F}]/u", "", $input);
  #$clean = preg_replace("/[^a-zA-Z\d\s\p{Thai}]/u", "", $input);
  return preg_replace("/[\r\n]/u", " ", $input);
}

## write messages
function writemsg($user_id, $message, $displayName, $pictureUrl, $groupId){
  $line = array($user_id, $message, time(), sanitizemsg($displayName), $pictureUrl, $groupId);
  $file = fopen(CSV_STORE_FILE, "a");
  fputcsv($file, $line);
  fclose($file);
}

?>

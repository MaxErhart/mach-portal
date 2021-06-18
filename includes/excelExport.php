<?PHP
  function cleanData(&$str)
  {
    $str = preg_replace("/\t/", "\\t", $str);
    $str = preg_replace("/\r?\n/", "\\n", $str);
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
  }

  function tabDelimited($data) {
    $string = "";
    $flag = false;
    foreach($data as $row) {
      if(!$flag) {
        $string .= implode("\t", array_keys($row)) . "\r\n";
        $flag = true;
      }
      array_walk($row, __NAMESPACE__ . '\cleanData');
      $string .= implode("\t", array_values($row)) . "\r\n";
    }
    return $string;
  }
?>
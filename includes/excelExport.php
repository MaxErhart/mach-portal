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

/**
 * @param string $filename
 * @return boolean Whether the string is a valid Windows filename.
 */
function isValidWindowsFilename($filename) {
  $regex = <<<'EOREGEX'
~                               # start of regular expression
^                               # Anchor to start of string.
(?!                             # Assert filename is not: CON, PRN, AUX, NUL, COM1, COM2, COM3, COM4, COM5, COM6, COM7, COM8, COM9, LPT1, LPT2, LPT3, LPT4, LPT5, LPT6, LPT7, LPT8, and LPT9.
  (CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])
  (\.[^.]*)?                  # followed by optional extension
  $                           # and end of string
)                               # End negative lookahead assertion.
[^<>:"/\\|?*\x00-\x1F]*         # Zero or more valid filename chars.
[^<>:"/\\|?*\x00-\x1F\ .]       # Last char is not a space or dot.
$                               # Anchor to end of string.
                              #
                              # tilde = end of regular expression.
                              # i = pattern modifier PCRE_CASELESS. Make the match case insensitive.
                              # x = pattern modifier PCRE_EXTENDED. Allows these comments inside the regex.
                              # D = pattern modifier PCRE_DOLLAR_ENDONLY. A dollar should not match a newline if it is the final character.
~ixD
EOREGEX;

  return preg_match($regex, $filename) === 1;
}

?>
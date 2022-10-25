<?php
class CacheHandler {
  private $branches = array();
  private $currentBranch = "";

  function __construct() {
    if(!array_key_exists("cache", $_SESSION)) {
      $_SESSION["cache"] = array();
    } else {
      foreach($_SESSION["cache"] as $key => $val) {
        array_push($this->branches, $key);
      }
    }
  }

  function push($key, $val) {
    if($this->currentBranch==="") {
      array_push($_SESSION["cache"], $val);
    }
    $_SESSION["cache"][$this->currentBranch][$key] = $val;
  }

  function pull() {
    if($this->currentBranch==="") {
      return $_SESSION["cache"];
    }
    return $_SESSION["cache"][$this->currentBranch];
  }

  function fork($key) {
    $branch = $this->currentBranch + "_" + $key;
    $this->currentBranch = $branch;
    array_push($this->branches, $branch);
    $_SESSION["cache"][$this->currentBranch] = array();
  }

  function checkout($branch, $create=false) {
    if(!in_array($branch, $this->branches) && !$create) {
      return false;
    } else if(!in_array($branch, $this->branches) && $create) {
      $this->currentBranch = $branch;
      array_push($this->branches, $branch);
      $_SESSION["cache"][$this->currentBranch] = array();
      return true;
    } else {
      $this->currentBranch = $branch;
      return true;
    }
  }

  function clear() {
    $_SESSION["cache"] = array();
    $this->branches = array();
    $this->currentBranch = "";
  }

}

class FormCacheHandler extends CacheHandler {
  function pushForm($formId, $form) {
    $this->checkout("forms_{$formId}", true);
    foreach($form as $key => $val) {
      $this->push($key, $val);
    }    
  }
  function pullForm($formId) {
    if(!$this->checkout("forms_{$formId}")) {
      return NULL;
    }
    return $this->pull();
  }  
  function pushElements($formId, $elements) {
    $this->checkout("forms_{$formId}_elements", true);
    foreach($elements as $key => $element) {
      $this->push($key, $element);
    }
  }
  function pullElements($formId) {
    if(!$this->checkout("forms_{$formId}_elements")) {
      return NULL;
    }
    return $this->pull();
  }
}
?>
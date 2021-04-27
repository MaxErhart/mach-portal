<?php

class user {
  private $isLoggedIn;
  private $fname;
  private $lname;
  private $groups;
  private $affiliation;
  private $mail;
  private $userRights = array(
    "theses" => array(
      "read" => array(
        // empty if everyone is allowed
      ),
      "write" => array(
        "groups" => array("MACH-Portal-Admin"),
        "users" => array()
      ), 
    ),
  );

  function __construct($sessionVariables) {
    if(!empty($sessionVariables)) {
      $this->isLoggedIn = $sessionVariables["isLoggedIn"];
      $this->fname = $sessionVariables["givenName"][0];
      $this->lname = $sessionVariables["sn"][0];
      $this->groups = $sessionVariables["memberOf"];
      $this->affiliation = $sessionVariables["affiliation"];
      $this->mail = $sessionVariables["mail"][0];
    }
  }

  function rightsOnTopic($topic, $action) {
    if(!empty($this->userRights[$topic][$action])) {
      foreach($this->groups as $group) {
        if(in_array($group, $this->userRights[$topic][$action]["groups"])) {
          return true;
        } else {
          continue;
        }
      }
      return false;
    } else {
      return true;
    }

  }

  function getFname() {
    if($this->isLoggedIn) {
      return $this->fname;
    } else {
      return NULL;
    }
  }

  function getLname() {
    if($this->isLoggedIn) {
      return $this->lname;
    } else {
      return NULL;
    }
  }

  function getGroups() {
    if($this->isLoggedIn) {
      return $this->groups;
    } else {
      return NULL;
    }
  }

  function getAffiliation() {
    if($this->isLoggedIn) {
      return $this->affiliation;
    } else {
      return NULL;
    }
  }

  function getMail() {
    if($this->isLoggedIn) {
      return $this->mail;
    } else {
      return NULL;
    }
  }

}

// $user = new user($_SESSION);
// print_r($user->rightsOnTopic("theses", "write"));
?>
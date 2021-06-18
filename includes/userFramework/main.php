<?php
include_once("D:\inetpub\MPortal\includes\dbFramework\main.php");
class user {
  private $schema;
  private $isLoggedIn;
  private $fname;
  private $lname;
  private $groups;
  private $affiliation;
  private $mail;
  private $userId;
  private $groupIds = array();
  // private $userRights = array(
  //   "theses" => array(
  //     "read" => array(
  //       // empty if everyone is allowed
  //     ),
  //     "write" => array(
  //       "groups" => array("MACH-Portal-Admin"),
  //       "users" => array()
  //     ), 
  //   ),
  // );
  private $groupRights = array();

  function __construct($sessionVariables) {
    $this->schema = new dbSchema("localhost", "mach-portal", "motor25", "mach_portal");
    if($sessionVariables == "webuser") {
      $webuser = $this->schema->selectTable("users")->select()->conditions(array("userId" => "4"))->get(1)[0];
      $this->fname = $webuser["firstname"];
      $this->lname = $webuser["lastname"];
      $this->groups = array();
      $this->isLoggedIn = false;
      $this->userId = 4;
    } else if(!empty($sessionVariables)) {
      $this->fname = $sessionVariables["givenName"][0];
      $this->lname = $sessionVariables["sn"][0];
      $this->groups = $sessionVariables["memberOf"];
      $this->affiliation = $sessionVariables["affiliation"];
      $this->mail = $sessionVariables["mail"][0];      
      $this->isLoggedIn = true;
      $this->userExistLocal();
      $this->usersGroupsExistLocal();
      $this->userMemberOfLocal();
    } else {
      $this->isLoggedIn = false;
    }   
  }

  function userExistLocal() {
    if($this->isLoggedIn) {
      $table = $this->schema->selectTable("users");
      $attributes = array(
        "firstname"=>$this->fname,
        "lastname"=>$this->lname,
        "mail"=>$this->mail
      );
      $dbUser = $table->select()->conditions($attributes)->get(1);
      if(empty($dbUser)) {
        $this->userId = $table->insert($attributes)->commit();
      } else {
        $this->userId = $dbUser[0]["userId"];
      }
      return $this->userId;
    } else {
      return NULL;
    }
  }

  function usersGroupsExistLocal() {
    $table = $this->schema->selectTable("groups");
    if($this->isLoggedIn) {
      foreach($this->groups as $group) {
        if(str_starts_with($group, "MACH-Portal")) {
          $attributes = array(
            "groupName"=>$group
          );
          $dbGroup = $table->select()->conditions($attributes)->get(1);
          if(empty($dbGroup)) {
            array_push($this->groupIds, $table->insert($attributes)->commit());
          } else {
            array_push($this->groupIds, $dbGroup[0]["groupId"]);
          }
        }

      }
    }
    
  }

  function userMemberOfLocal() {
    $table = $this->schema->selectTable("group_members");
    $conditions = array(
      "userId"=>$this->userId
    );
    $userMemberOfLocal = $table->select("groupMemberId", "groupId")->conditions($conditions)->getAll();
    $userMemberOfLocalGroupId = array_column($userMemberOfLocal, "groupId");
    if(empty($userMemberOfLocal)) {
      foreach($this->groupIds as $groupId) {
        $attributes = array(
          "groupId"=>$groupId,
          "userId"=>$this->userId
        );
        $table->insert($attributes)->commit();
      }
    } else {
      foreach($this->groupIds as $groupId) {
        if(!in_array($groupId, $userMemberOfLocalGroupId)) {
          $attributes = array(
            "groupId"=>$groupId,
            "userId"=>$this->userId
          );
          $table->insert($attributes)->commit();
        }
      }
      foreach($userMemberOfLocal as $localMemberOf) {
        if(!in_array($localMemberOf["groupId"], $this->groupIds)) {
          $table->delete($localMemberOf["groupMemberId"])->commit();
        }
      }
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

  function getGroupRights() {
    if(!empty($this->groups)) {
      $table = $this->schema->selectTable("group_rights")->innerJoin("groupId", "groups", "groupId", array("groupName"))->innerJoin("featureId", "features", "featureId", array("featureName"))->select("rights", "rightsTarget")->orConditions(array("groupId" => $this->groupIds));
      $rights = $table->getAll();
      for($i=0; $i<count($rights); $i++) {
        $rights[$i]["rightsTarget"] = json_decode($rights[$i]["rightsTarget"], true);
      }
  
      $temp = array();
  
      foreach($rights as $right) {
        if(array_key_exists($right["featureName"], $temp)) {
          if($right["rights"] == "w") {
            $temp[$right["featureName"]]["write"]["users"] = array_unique(array_merge($temp[$right["featureName"]]["write"]["users"], $right["rightsTarget"]["users"]));
            $temp[$right["featureName"]]["write"]["groups"] = array_unique(array_merge($temp[$right["featureName"]]["write"]["groups"], $right["rightsTarget"]["groups"]));
          } else if($right["rights"] == "r") {
            $temp[$right["featureName"]]["read"]["users"] = array_unique(array_merge($temp[$right["featureName"]]["read"]["users"], $right["rightsTarget"]["users"]));
            $temp[$right["featureName"]]["read"]["groups"] = array_unique(array_merge($temp[$right["featureName"]]["read"]["groups"], $right["rightsTarget"]["groups"]));
          }
  
        } else {
          if($right["rights"] == "w") {
            $temp[$right["featureName"]] = array("write" => $right["rightsTarget"], "read" => array("users" => array(), "groups" => array()));
          } else if($right["rights"] == "r") {
            $temp[$right["featureName"]] = array("write" => array("users" => array(), "groups" => array()), "read" => $right["rightsTarget"]);
          }
          
        }
  
      }
    
  
      return $temp;
    } else {
      return NULL;
    }

  }

  function getUserRights() {  
    $table = $this->schema->selectTable("user_rights")->innerJoin("userId", "users", "userId", array("firstname", "lastname"))->innerJoin("featureId", "features", "featureId", array("featureName"))->select("rights", "rightsTarget")->conditions(array("userId" => $this->userId));
    $rights = $table->getAll();

    for($i=0; $i<count($rights); $i++) {
      $rights[$i]["rightsTarget"] = json_decode($rights[$i]["rightsTarget"], true);
    }

    $temp = array();

    foreach($rights as $right) {
      if(array_key_exists($right["featureName"], $temp)) {
        if($right["rights"] == "w") {
          $temp[$right["featureName"]]["write"]["users"] = array_unique(array_merge($temp[$right["featureName"]]["write"]["users"], $right["rightsTarget"]["users"]));
          $temp[$right["featureName"]]["write"]["groups"] = array_unique(array_merge($temp[$right["featureName"]]["write"]["groups"], $right["rightsTarget"]["groups"]));
        } else if($right["rights"] == "r") {
          $temp[$right["featureName"]]["read"]["users"] = array_unique(array_merge($temp[$right["featureName"]]["read"]["users"], $right["rightsTarget"]["users"]));
          $temp[$right["featureName"]]["read"]["groups"] = array_unique(array_merge($temp[$right["featureName"]]["read"]["groups"], $right["rightsTarget"]["groups"]));
        }

      } else {
        if($right["rights"] == "w") {
          $temp[$right["featureName"]] = array("write" => $right["rightsTarget"], "read" => array("users" => array(), "groups" => array()));
        } else if($right["rights"] == "r") {
          $temp[$right["featureName"]] = array("write" => array("users" => array(), "groups" => array()), "read" => $right["rightsTarget"]);
        }
        
      }

    }
  

    return $temp;
  }

  function getRights() {
    if(!empty($this->groups)) {
      $rights = array();
      $groupRights = $this->getGroupRights();
      $userRights = $this->getUserRights();
      foreach(array_unique(array_merge(array_keys($groupRights), array_keys($userRights))) as $topic) {
        $rights[$topic] = array();
        if(array_key_exists($topic, $groupRights)) {
          if(array_key_exists($topic, $userRights)) {
            $rights[$topic]["write"]["users"] = array_unique(array_merge($groupRights[$topic]["write"]["users"], $groupRights[$topic]["write"]["users"]));
            $rights[$topic]["read"]["users"] = array_unique(array_merge($groupRights[$topic]["read"]["users"], $groupRights[$topic]["read"]["users"]));
            $rights[$topic]["write"]["groups"] = array_unique(array_merge($groupRights[$topic]["write"]["groups"], $groupRights[$topic]["write"]["groups"]));
            $rights[$topic]["read"]["groups"] = array_unique(array_merge($groupRights[$topic]["read"]["groups"], $groupRights[$topic]["read"]["groups"]));
          } else {
            $rights[$topic] = $groupRights[$topic];   
          }
        } else if(array_key_exists($topic, $userRights)) {
          $rights[$topic] = $userRights[$topic];
        }
      }
      return $rights;
    } else {
      return $this->getUserRights();
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

  function userInformation() {
    $user = array(
      "fname" => $this->fname,
      "lname" => $this->lname,
      "mail" => $this->mail,
      "affiliation" => $this->affiliation,
      "groups" => $this->groupIds,
      "rights" => $this->getRights(),
      "userId" => $this->userId
    );
    return $user;
  }

  public function __toString() {
    $user = array(
      "fname" => $this->fname,
      "lname" => $this->lname,
      "mail" => $this->mail,
      "affiliation" => $this->affiliation,
      "groups" => $this->groups,
      "userRights" => $this->userRights,
      "groupRights" => $this->groupRights
    );
    return json_encode($user);
  }
  
}

// $user = new user($_SESSION);
// echo json_encode($user->getRights());
// print_r($user->getRights());
?>
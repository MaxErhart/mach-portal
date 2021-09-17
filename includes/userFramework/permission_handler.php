<?php
include_once("D:\inetpub\MPortal\includes\dbFramework\main.php");
class PermissionHandler extends dbSchema{

  const FOREIGN_OBJECT_TYPES_DBINFO = array(
    "form" => array(
      "type" => "form",
      "table" => "forms",
      "joinColumn" => "formId",
      "colValTypes" => array(
        "dateOfCreation" => "date",
        "targetUsers" => "JSON"
      )
    ),
    "submission" => array(
      "type" => "submission",
      "table" => "form_submissions",
      "joinColumn" => "formSubmissionId",
      "colValTypes" => array(
        "dateOfSubmission" => "date",
        "data" => "JSON",
        "files" => "JSON"
      )      
    ),    
  );
  const OBJECT_COLUMNS = array(
    "foreign_objectId",
    "objectType",
    "ownerId",
    "status"
  );
  public $query = "";

  public static function connectLocal() {
    return new PermissionHandler("localhost", "mach-portal", "motor25", "mach_portal");
  }

  function joinTables($objectType) {
    $foreignObjectTypeDbInfo = self::FOREIGN_OBJECT_TYPES_BDINFO[$objectType];
    $query = "
      SELECT *
      FROM `objects`
      INNER JOIN `permissions`
      ON `objects`.`objectId` = `permissions`.`objectId`
      INNER JOIN `{$foreignObjectTypeDbInfo["table"]}`
      ON `objects`.`foreign_objectId` = `{$foreignObjectTypeDbInfo["table"]}`.`{$foreignObjectTypeDbInfo["joinColumn"]}`
      WHERE `objects`.`objectType`='{$foreignObjectTypeDbInfo["type"]}'   
    ";
    $this->query .= $query;
  }

  function permissionConditions($userId, $groupIds) {
    $query = "
      AND (
        `objects`.`ownerId`='{$userId}'
        OR `objects`.`status`='public' 
        OR (
          `objects`.`status`='restricted'
          AND (
            `permissions`.`userId`='{$userId}'
            OR `permissions`.`groupId` in {$this->mysqlListFormat($groupIds)}
          )
        )
      )    
    ";
    $this->query .= $query;
  }

  function getObjectColumns() {
    return self::OBJECT_COLUMNS;
  }

  function defaultGrouping() {
    $this->query .= "GROUP BY `permissions`.`permissionType`, `permissions`.`objectId`";
  }

  function getAll($colTypePairs=NULL) {
    $queryReturnVals = array();
    if($result = $this->connection->query($this->query)) {
      while($row = $result->fetch_assoc()) {
        if($colTypePairs!=NULL) {
          foreach($colTypePairs as $col => $type) {
            if($type=="JSON") {
              $row[$col] = json_decode($row[$col], true);
            } else if($type=="date") {
              if(!$row[$col]==NULL) {
                $dateTime = new DateTime($row[$col]);
                $dateTime = $dateTime->format('d.m.Y');              
                $row[$col] = $dateTime;
              }
            }
          }
        }        
        array_push($queryReturnVals, $row);
      }
      return array("error" => NULL, "result" => $queryReturnVals);
    } else {
      return array("error" => $this->connection->error);
    }     
  }

  function getObjects($objectType, $userId, $groupIds, $condition=NULL) {
    if($condition != NULL) {
      $queryCondition = strval($condition);
    } else {
      $queryCondition = "";
    }
    $this->joinTables($objectType);
    $this->query .= $queryCondition;
    if(!$admin) {
      $this->permissionConditions($userId, $groupIds);     
    }
    $this->defaultGrouping();
    return $this->getAll(self::FOREIGN_OBJECT_TYPES_DBINFO[$objectType]["colValTypes"]);
  }

  function getSpecificObject($objectType, $userId, $groupIds, $foreignObjectId) {
    $this->joinTables($objectType);
    $specificObjectCondition = new Condition("AND", "objects", "foreign_objectId", array($foreignObjectId));
    $this->query .= strval($specificObjectCondition);
    if(!$admin) {
      $this->permissionConditions($userId, $groupIds);     
    }
    $this->defaultGrouping();
    return $this->getAll(self::FOREIGN_OBJECT_TYPES_DBINFO[$objectType]["colValTypes"]);
  }

  function checkMissingKeyData($objectData, $missingKey, $value) {
    if(in_array($missingKey, $objectData)) {
      return $objectData;
    } else {
      $objectData[$missingKey] = $value;
      return $objectData;
    }
  }

  function insertObject($foreignObjectData, $objectData, $permissionsData) {
    $foreignObjectTypeDbInfo = self::FOREIGN_OBJECT_TYPES_DBINFO[$objectData["objectType"]];
    $objectData = $this->checkMissingKeyData($objectData, "foreign_objectId", "LAST_INSERT_ID()");
    $permissionsData = $this->checkMissingKeyData($permissionsData, "objectId", "LAST_INSERT_ID()");
    $queries = array();

    $queries[0] = "
      INSERT INTO `{$foreignObjectTypeDbInfo["table"]}` {$this->mysqlListFormat(array_keys($foreignObjectData), "TABLE")}
      VALUES {$this->mysqlListFormat($foreignObjectData)}
    ";
    $queries[1] = "
      INSERT INTO `objects` {$this->mysqlListFormat(array_keys($objectData), "TABLE")}
      VALUES {$this->mysqlListFormat($objectData)}
    ";
    $queries[2] = "
      INSERT INTO `permissions` {$this->mysqlListFormat(array_keys($permissionsData),"TABLE")}
      VALUES {$this->mysqlListFormat($permissionsData)}
    ";
    $this->transaction($queries);
  
  }
    
}

// $permissionHandler = PermissionHandler::connectLocal();
// $condition = new Condition("AND", "form_submissions", "formId", array("70"));
// $permissionHandler->getObjects("submission", "3", array("25"), $condition);
// print_r($permissionHandler->getSpecificObject("form", "3", array("25"), "65"));
// $condition = new Condition("OR", "tab", "col", array(1, 2));
// echo $condition;
// $foreignObjectData = array(
//   "formName" => "permformname",
//   "userId" => 3,
//   "multipleSubmissions" => "1"
// );
// $objectData = array(
//   "objectType" => "form",
//   "ownerId" => 3,
//   "status" => "public"
// );
// $permissionsData = array(
//   "permissionType" => "readonly_form",
//   "groupId" => 25
// );
// $permissionHandler->insertObject($foreignObjectData, $objectData, $permissionsData);

?>
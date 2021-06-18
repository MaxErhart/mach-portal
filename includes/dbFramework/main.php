<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


class dbSchema {
  private $connection;

  public function __construct($dbAddress, $dbUser, $dbPassword, $dbName) {
    $this->connection = new mysqli($dbAddress, $dbUser, $dbPassword, $dbName);
  }

  function selectTable($tableName) {
    $table = new dbTable();
    $table->setConnection($this->connection);
    $table->setTable($tableName);
    return $table;
  }

  function getConnection() {
    return $this->connection;
  }

  function getUserIds($rights) {
    if(in_array("all", $rights["write"]["users"])) {
      return array("write" => "all", "read" => "all");
    } else if(in_array("all", $rights["read"]["users"])) {
      $conditions = array(
        "groupId" => $rights["write"]["groups"]
      );
      $groupWriteIds = $this->selectTable("group_members")->select("userId")->conditions($conditions)->getAll();
      
      
      $writeIds = array();
      if($groupWriteIds != NULL) {
        $groupWriteIds = array_column($groupWriteIds, "userId");
        $writeIds = array_unique(array_merge($groupWriteIds, $rights["write"]["users"]));
      } else {
        $writeIds = $rights["write"]["users"];
      }          
      return array("write" => $writeIds, "read" => "all");
    } else {
      $conditions = array(
        "groupId" => $rights["read"]["groups"]
      );
      $groupReadIds = $this->selectTable("group_members")->select("userId")->conditions($conditions)->getAll();
      $readIds = array();
  
      if($groupReadIds != NULL) {
        $groupReadIds = array_column($groupReadIds, "userId");
        $readIds = array_unique(array_merge($groupReadIds, $rights["read"]["users"]));
      } else {
        $readIds = $rights["read"]["users"];
      }
      
  
      $conditions = array(
        "groupId" => $rights["write"]["groups"]
      );
      $groupWriteIds = $this->selectTable("group_members")->select("userId")->conditions($conditions)->getAll();
      
      
      $writeIds = array();
      if($groupWriteIds != NULL) {
        $groupWriteIds = array_column($groupWriteIds, "userId");
        $writeIds = array_unique(array_merge($groupWriteIds, $rights["write"]["users"]));
      } else {
        $writeIds = $rights["write"]["users"];
      }
    
      $readIds = array_unique(array_merge($writeIds, $readIds));
      return array("write" => $writeIds, "read" => $readIds);
    }


  }

}

class dbTable {
  private $connection;
  private $table;
  private $joinConditions = array();
  private $foreignSelects = array();
  public function __construct($dbAddress=NULL, $dbUser=NULL, $dbPassword=NULL, $dbName=NULL, $table=NULL) {
    if($dbAddress!=NULL && $dbUser!=NULL && $dbPassword!=NULL && $dbName!=NULL && $table!=NULL) {
      $this->connection = new mysqli($dbAddress, $dbUser, $dbPassword, $dbName);
      $this->table = $table;
    }
  }
  
  function setConnection($connection) {
    $this->connection = $connection;
  }

  function setTable($table) {
    $this->table = $table;
  }

  function select() {
    $args = func_get_args();
    if(empty($this->foreignSelects)) {
      $selectQuery =  new selectQuery($args, $this->table, $this->connection);
      return $selectQuery;
    } else {
      $selectQuery = new selectQuery($args, $this->table, $this->connection, $this->joinConditions, $this->foreignSelects);
      return $selectQuery;
    }

  }

  function innerJoin($tableCol, $foreignTable, $foreignCol, $foreignSelects) {
    $condition = array();
    $condition["localCol"] = $tableCol;
    $condition["foreignTable"] = $foreignTable;
    $condition["foreignCol"] = $foreignCol;
    $this->foreignSelects[$foreignTable] = $foreignSelects;
    array_push($this->joinConditions, $condition);
    return $this;
  }

  function insert($columnValuePairs) {
    $insertQuery = new insertQuery($columnValuePairs, $this->table, $this->connection);
    return $insertQuery;
  }

  function delete($id) {
    $deleteQuery = new deleteQuery($id, $this->table, $this->connection);
    return $deleteQuery;
  }

  function deleteNoPK($keyValuePairs) {
    $deleteQueryNoPK = new deleteQueryNoPK($keyValuePairs, $this->table, $this->connection);
    return $deleteQueryNoPK;
  }

  function update($keyValuePairs, $condition) {
    $updateQuery = new updateQuery($keyValuePairs, $condition, $this->table, $this->connection);
    return $updateQuery;
  }

}

class updateQuery {
  private $connection;
  private $table;
  private $query;
  
  function __construct($keyValuePairs, $condition, $table, $connection) {
    $this->connection = $connection;
    $this->table = $table;
    // print_r($keyValuePairs);
    // print_r($condition);
    if(!empty($keyValuePairs) && !empty($condition)) {
      $query = "UPDATE `".$table."` SET";
      $first = true;
      foreach($keyValuePairs as $col => $val) {
        if($first) {
          $first = false;
          $query .= " `".$col."`='".$val."'";
        } else {
          $query .= ", `".$col."`='".$val."'";
        }
      }
      $first = true;
      foreach($condition as $col => $val) {
        if($first) {
          $first = false;
          $query .= " WHERE `".$col."`='".$val."'";
        } else {
          $query .= " AND `".$col."`='".$val."'";
        }
        
      }
      $this->query = $query;
    }   
  }

  function commit() {
    if($result = $this->connection->query($this->query)) {
      return array("error" => NULL);
    } else {
      return array("error" => $this->connection->error);
    }    
  }

}


class deleteQueryNoPK {
  private $connection;
  private $table;
  private $query;

  function __construct($keyValuePairs, $table, $connection) {
    $this->connection = $connection;
    $this->table = $table;
    if(!empty($keyValuePairs)) {
      $query = "DELETE FROM `".$table."` WHERE";
      $first = true;
      foreach($keyValuePairs as $col=>$val) {
        if($first){
          $first = false;
          $query .= " `".$col."`='".$val."'";
        } else {
          $query .= " AND `".$col."`='".$val."'";
        }
      }
      $this->query = $query;
    }
  }

  function commit() {
    if($result = $this->connection->query($this->query)) {
      return array("error" => NULL);
    } else {
      return array("error" => $this->connection->error);
    }
  }

}

class deleteQuery {
  private $connection;
  private $table;
  private $query;

  function __construct($id, $table, $connection) {
    $this->connection = $connection;
    $this->table = $table;
    if(!is_null($id)) {
      foreach($id as $key=>$val) {
        $query = "DELETE FROM `".$table."` WHERE `".$key."`='".$val."'";
      }
      
      $this->query = $query;
    }
  }

  function commit() {
    if($result = $this->connection->query($this->query)) {
      return array("error" => NULL);
    } else {
      return array("error" => $this->connection->error);
    }
  }

}

class insertQuery {
  private $connection;
  private $table;
  private $query;
  function __construct($columnValuePairs, $table, $connection) {
    $this->table = $table;
    $this->connection = $connection;
    if(!empty($columnValuePairs) && is_array($columnValuePairs)) {
      $first = true;
      $query = "INSERT INTO `".$table."`";
      $values = ") VALUES";
      foreach($columnValuePairs as $col=>$val) {
        if($first) {
          $first = false;
          $query .= " (`".$col."`";
          $values .= " ('".$val."'";
        } else {
          $query .= ",`".$col."`";
          $values .= ",'".$val."'";
        }
      }
      $query .= $values.")";
    }
    $this->query = $query;
  }

  function commit() {
    if($result = $this->connection->query($this->query)) {
      return $this->connection->insert_id;
    } else {
      return array("error" => $this->connection->error);
    }
  }

}

class selectQuery {
  private $args;
  private $table;
  private $query;
  private $connection;
  private $joinConditions;
  private $foreignSelects;

  function __construct($args, $table, $connection, $joinConditions=NULL, $foreignSelects=NULL) {
    $this->args = $args;
    $this->table = $table;
    $this->connection = $connection;
    $this->joinConditions = $joinConditions;
    $this->foreignSelects = $foreignSelects;
    if($joinConditions==NULL || $foreignSelects==NULL) {
      if(empty($args)) {
        $query = "SELECT * FROM `".$this->table."`";
        $this->query = $query;
      } else {      
        $query = "SELECT ";
        $first = true;
        foreach($args as $arg) {
          if($first) {
            $first = false;
            $query .= "`".$arg."`";
          } else {
            $query .= ",`".$arg."`";
          }
        }
        $query .= " FROM `".$this->table."`";
        $this->query = $query;
      }    
    } else {
      if(empty($args)) {
        $query = "SELECT";
        $first = true;
        foreach($this->foreignSelects as $table=>$cols) {
          foreach($cols as $col) {
            if($first) {
              $first=false;
              $query .= " `".$table."`.`".$col."`";
            } else {
              $query .= " ,`".$table."`.`".$col."`";
            }
          }
            
        }
        $query .= " FROM `".$this->table."`";
        foreach($this->joinConditions as $condition) {
          $query .= " INNER JOIN `".$condition["foreignTable"]."` ON `".$this->table."`.`".$condition["localCol"]."` = `".$condition["foreignTable"]."`.`".$condition["foreignCol"]."`";
         
        }
        // echo $query;
        $this->query = $query;
      } else {
        $query = "SELECT";
        $first = true;
        foreach($this->args as $col) {
          if($first) {
            $first = false;
            $query .= " `".$this->table."`.`".$col."`";
          } else {
            $query .= " ,`".$this->table."`.`".$col."`";
          }
        }
        foreach($this->foreignSelects as $table=>$cols) {
          foreach($cols as $col) {
            if($first) {
              $first=false;
              $query .= " `".$table."`.`".$col."`";
            } else {
              $query .= " ,`".$table."`.`".$col."`";
            }
          }
        }
        $query .= " FROM `".$this->table."`";
        foreach($this->joinConditions as $condition) {
          $query .= " INNER JOIN `".$condition["foreignTable"]."` ON `".$this->table."`.`".$condition["localCol"]."` = `".$condition["foreignTable"]."`.`".$condition["foreignCol"]."`"; 
        }
        // echo $query;
        $this->query = $query;
      }
    }
  }

  function orConditions($conditions) {
    if(!empty($conditions) && is_array($conditions)) {
      $this->query .= " WHERE ";
      $first = true;

      foreach($conditions as $col=>$vals) {
        foreach($vals as $val) {
          if($first) {
            $first = false;
            $this->query .= "`".$this->table."`.`".$col."` = '".$val."'";
          } else {
            $this->query .= " OR `".$this->table."`.`".$col."` = '".$val."'";
          }
        }
      }
      // echo $this->query;
      return $this;
    } else {
      return NULL;
    }
  }

  function conditions($conditions, $range=NUll) {
    if($range==NULL) {
      if(!empty($conditions) && is_array($conditions)) {
        $this->query .= " WHERE (";
        $firstCol = true;
  
        foreach($conditions as $col=>$vals) {
          if($firstCol) {
            $firstCol = false;
            if(is_array($vals)) {
              $firstVal = true;
              foreach($vals as $val) {
                if($firstVal) {
                  $firstVal = false;
                  $this->query .= "`".$this->table."`.`".$col."` = '".$val."'";
                } else {
                  $this->query .= " OR `".$this->table."`.`".$col."` = '".$val."'";
                }
              }
              
            } else {
              $this->query .= "`".$this->table."`.`".$col."` = '".$vals."'";
            }
            $this->query .= ")";
          } else {
            if(is_array($vals)) {
              $firstVal = true;
              foreach($vals as $val) {
                if($firstVal) {
                  $firstVal = false;
                  $this->query .= " AND (`".$this->table."`.`".$col."` = '".$val."'";
                } else {
                  $firstVal = false;
                  $this->query .= " OR `".$this->table."`.`".$col."` = '".$val."'";
                }
  
              }
            } else {
              $this->query .= " AND (`".$this->table."`.`".$col."` = '".$vals."'";
            }
            $this->query .= ")";
          }
  
        }
        return $this;
      } else {
        return NULL;
      }
    } else {
      if(!empty($conditions) && is_array($conditions)) {
        $this->query .= " WHERE (";
        $firstCol = true;
  
        foreach($conditions as $col=>$vals) {
          if($firstCol) {
            $firstCol = false;
            if(is_array($vals)) {
              $firstVal = true;
              foreach($vals as $val) {
                if($firstVal) {
                  $firstVal = false;
                  $this->query .= "`".$this->table."`.`".$col."` = '".$val."'";
                } else {
                  $this->query .= " OR `".$this->table."`.`".$col."` = '".$val."'";
                }
              }
              
            } else {
              $this->query .= "`".$this->table."`.`".$col."` = '".$vals."'";
            }
            $this->query .= ")";
          } else {
            if(is_array($vals)) {
              $firstVal = true;
              foreach($vals as $val) {
                if($firstVal) {
                  $firstVal = false;
                  $this->query .= " AND (`".$this->table."`.`".$col."` = '".$val."'";
                } else {
                  $firstVal = false;
                  $this->query .= " OR `".$this->table."`.`".$col."` = '".$val."'";
                }
  
              }
            } else {
              $this->query .= " AND (`".$this->table."`.`".$col."` = '".$vals."'";
            }
            $this->query .= ")";
          }
  
        }
        foreach($range as $col => $vals) {
          if(is_array($vals)) {
            $this->query .= " AND (((`".$this->table."`.`".$col."`>='".$vals[0]."') AND (`".$this->table."`.`".$col."`<'".$vals[1]."')) OR `".$this->table."`.`".$col."` IS NULL)";
          } else {
            $this->query .= " AND ((`".$this->table."`.`".$col."`>='".$vals."') OR `".$this->table."`.`".$col."` IS NULL)";
          }
          
        }
        // echo $this->query;
        return $this;
      } else {
        $this->query .= " WHERE ";
        foreach($range as $col => $vals) {
          if(is_array($vals)) {
            $this->query .= "((`".$this->table."`.`".$col."`>='".$vals[0]."') AND (`".$this->table."`.`".$col."`<'".$vals[1]."')) OR `".$this->table."`.`".$col."` IS NULL";
          } else {
            $this->query .= "(`".$this->table."`.`".$col."`>='".$vals."') OR `".$this->table."`.`".$col."` IS NULL";
          }          
          
        }
        // echo $this->query;
        // echo "2";
        return $this;    
      }      
    }
    
  }

  function orderBy($colName, $direction) {
    $this->query .= " ORDER BY `".$colName."` ".$direction;
    return $this;
  }

  function get($amount, $offset=0, $colTypePairs=NULL) {
    $this->query .= " LIMIT ".$offset.", ".$amount;
    $queryReturnVals = array();
    if($result = $this->connection->query($this->query)) {
      while($row = $result->fetch_assoc()) {    
        if($colTypePairs!=NULL) {
          foreach($colTypePairs as $col => $type) {
            if($type=="JSON") {
              $row[$col] = json_decode($row[$col], true);
            } else if($type=="date") {
              $dateTime = new DateTime($row[$col]);
              $dateTime = $dateTime->format('d.m.Y');              
              $row[$col] = $dateTime;
            }
          }
        }
        array_push($queryReturnVals, $row);
      }
    }
    // echo $this->query;
    return $queryReturnVals;
  }

  function getAll($colTypePairs=NULL) {
  	// echo $this->query;
    $queryReturnVals = array();
    $once = NULL;
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
            } else if($type=="once") {
              if($once==NULL) {
                $once = $row[$col];
              }
              unset($row[$col]);
            }
          }
        }        
        array_push($queryReturnVals, $row);
      }
      if($once != NULL) {
        return array($once, $queryReturnVals);
      } else {
        return $queryReturnVals;
      }
      
    }
  }  

}


// $serverName = "localhost";
// $dbName = "mach_users";
// $user = "mach-portal";
// $dbPassword = "motor25";

// $test = new dbSchema($serverName, $user, $dbPassword, $dbName);
// $colTypePairs = array(
//   "dateOfCreation" => "date",
//   "deadline" => "date"
// );    
// $conditions = array(
//   "userId" => "3"
// );
// $range = array(
//   "deadline" => strval(date("y-m-d"))
// );
// $test->selectTable("forms")->select()->conditions($conditions, $range)->orderBy("dateOfCreation", "DESC")->getAll($colTypePairs);

// print_r($test->getALL());
?>
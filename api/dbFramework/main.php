<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class dbSchema {
  private $connection;
  private $schema;
  public function __construct($dbAddress, $dbUser, $dbPassword, $dbName, $schemaName) {
    $this->connection = new mysqli($dbAddress, $dbUser, $dbPassword, $dbName);
    $this->schema = $schemaName;
  }
  
  function select() {
    $args = func_get_args();
    $selectQuery =  new selectQuery($args, $this->schema, $this->connection);
    return $selectQuery;
  }

  function insert($columnValuePairs) {
    $insertQuery = new insertQuery($columnValuePairs, $this->schema, $this->connection);
    return $insertQuery;
  }

  function delete($id) {
    $deleteQuery = new deleteQuery($id, $this->schema, $this->connection);
    return $deleteQuery;
  }

  function deleteNoPK($keyValuePairs) {
    $deleteQueryNoPK = new deleteQueryNoPK($keyValuePairs, $this->schema, $this->connection);
    return $deleteQueryNoPK;
  }

}

class deleteQueryNoPK {
  private $connection;
  private $schema;
  private $query;

  function __construct($keyValuePairs, $schema, $connection) {
    $this->connection = $connection;
    $this->schema = $schema;
    if(!empty($keyValuePairs)) {
      $query = "DELETE FROM `".$schema."` WHERE";
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
      return array("error" => $connection->error);
    }
  }

}

class deleteQuery {
  private $connection;
  private $schema;
  private $query;

  function __construct($id, $schema, $connection) {
    $this->connection = $connection;
    $this->schema = $schema;
    if(!is_null($id)) {
      $query = "DELETE FROM `".$schema."` WHERE `id`='".$id."'";
      $this->query = $query;
    }
  }

  function commit() {
    if($result = $this->connection->query($this->query)) {
      return array("error" => NULL);
    } else {
      return array("error" => $connection->error);
    }
  }

}

class insertQuery {
  private $connection;
  private $schema;
  private $query;

  function __construct($columnValuePairs, $schema, $connection) {
    $this->schema = $schema;
    $this->connection = $connection;
    if(!empty($columnValuePairs) && is_array($columnValuePairs)) {
      $first = true;
      $query = "INSERT INTO `".$schema."`";
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
      return array("error" => NULL);
    } else {
      return array("error" => $connection->error);
    }
  }

}

class selectQuery {
  private $args;
  private $schema;
  private $query;
  private $connection;

  function __construct($args, $schema, $connection) {
    $this->args = $args;
    $this->schema = $schema;
    $this->connection = $connection;
    if(empty($args)) {
      $query = "SELECT * FROM `".$this->schema."`";
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
      $query .= " FROM `".$this->schema."`";
      $this->query = $query;
    }    
  }

  function conditions($conditions) {
    if(!empty($conditions) && is_array($conditions)) {
      $this->query .= " WHERE ";
      $first = true;
      foreach($conditions as $col=>$val) {
        if($first) {
          $first = false;
          $this->query .= "`".$col."` = '".$val."'";
        } else  {
          $this->query .= " AND `".$col."` = '".$val."'";
        }
      }
      return $this;
    } else {
      return NULL;
    }
  }
 
  function get($amount, $offset=0) {
    $this->query .= " LIMIT ".$offset.", ".$amount;
    $queryReturnVals = array();
    if($result = $this->connection->query($this->query)) {
      while($row = $result->fetch_assoc()) {
        array_push($queryReturnVals, $row);
      }
      return $queryReturnVals;
    }
  }

  function getAll() {
    $queryReturnVals = array();
    if($result = $this->connection->query($this->query)) {
      while($row = $result->fetch_assoc()) {
        array_push($queryReturnVals, $row);
      }
      return $queryReturnVals;
    }
  }  

}

// $serverName = "localhost";
// $dbName = "forms";
// $user = "mach-portal";
// $dbPassword = "motor25";

// $test = new dbSchema($serverName, $user, $dbPassword, $dbName, "forms");
// $test->deleteNoPK(array("test"=>"val"));

?>
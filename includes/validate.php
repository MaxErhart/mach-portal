<?php
include_once("D:\inetpub\MPortal\includes\dbFramework\main.php");
include_once("D:\inetpub\MPortal\includes\userFramework\main.php");
include_once("D:\inetpub\MPortal\includes\userFramework\cache_handler.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// session_start();
class Form {
  const INPUT_ELEMENTS = array(
    "InputElement",
    "SelectionElement",
    "FileUploadElement",
  );
  protected $id;
  private $name;
  private $userId;
  private $dateOfCreation;
  private $deadline;
  private $multipleSubmissions;
  private $dateLastUpdate;
  private $elements = array();

  private $isValidated = false;
  private $isValid = false;

  private $dbConnection;

  function __construct($formId) {
    if($this->buildFromCache($formId)){
      return NULL;
    }
    $dbSchema = $this->connectDB();
    $condition = array(
      "formId" => $formId
    );
    $colTypePairs = array(
      "dateOfCreation" => "date",
      "deadline" => "date",
      "dateLastUpdate" => "date",
    );    
    $form = $dbSchema->selectTable("forms")->select()->conditions($condition)->getAll($colTypePairs)[0];
    if(!$form) {
      return NULL;
    }
    $this->id = $formId;
    $this->name = $form["formName"];
    $this->userId = $form["userId"];
    $this->dateOfCreation = $form["dateOfCreation"];
    $this->deadline = $form["deadline"];
    $this->multipleSubmissions = $form["multipleSubmissions"];
    $this->dateLastUpdate = $form["dateLastUpdate"];

    $condition = array(
      "formId" => $formId
    );
    $colTypePairs = array(
      "data" => "JSON",
    );
    $elements = $dbSchema->selectTable("form_elements")->select()->conditions($condition)->getAll($colTypePairs);
    foreach($elements as $element) {
      if(!in_array($element["component"], self::INPUT_ELEMENTS)){
        continue;
      }
      $formElement = new $element["component"]($element["formElementId"], $element["position"], $element["elementId"], $element["data"]);
      array_push($this->elements, $formElement);
    }
    $this->cache();

  }

  function connectDB() {
    if(!$this->dbConnection) {
      $serverName = "localhost";
      $dbName = "mach_portal";
      $user = "mach-portal";
      $dbPassword = "motor25";
      $dbSchema = new dbSchema($serverName, $user, $dbPassword, $dbName);
      $this->dbConnection = $dbSchema;
    }
    return $this->dbConnection;  
  }

  function clear() {
    unset($_SESSION["cache"]["form_{$this->id}"]);
  }

  function cache() {
    if(!array_key_exists("cache", $_SESSION)) {
      $_SESSION["cache"] = array();
    }
    $elements = array();
    foreach($this->elements as $element) {
      array_push($elements, $element->repr());
    }
    $_SESSION["cache"]["form_{$this->id}"] = array(
      "id"=>$this->id,
      "name"=>$this->name,
      "userId"=>$this->userId,
      "dateOfCreation"=>$this->dateOfCreation,
      "deadline"=>$this->deadline,
      "multipleSubmissions"=>$this->multipleSubmissions,
      "dateLastUpdate"=>$this->dateLastUpdate,
      "elements"=>$elements,
    ); 
  }

  function buildFromCache($id) {
    if(!array_key_exists("cache", $_SESSION)) {
      return false;
    }
    if(!array_key_exists("form_{$id}", $_SESSION["cache"])) {
      return false;
    }
    $form =  $_SESSION["cache"]["form_{$id}"];
    $this->id = $form["id"];
    $this->name = $form["name"];
    $this->userId = $form["userId"];
    $this->dateOfCreation = $form["dateOfCreation"];
    $this->deadline = $form["deadline"];
    $this->multipleSubmissions = $form["multipleSubmissions"];
    $this->dateLastUpdate = $form["dateLastUpdate"];
    foreach($form["elements"] as $element) {
      $formElement = new $element["component"]($element["formElementId"],$element["position"],$element["elementId"],$element);
      array_push($this->elements, $formElement);
    }
    return true;
  }

  function isValid() {
    if(!$this->isValidated) {
      throw new Exception("Form not validated.");
    }
    $this->isValidated = false;
    return $this->isValid;
  }

  function validate($values) {
    $validation = array();
    $this->isValid = true;
    foreach($this->elements as $element) {
      $elementId = $element->elementId;
      if(!array_key_exists($elementId, $values)) {
        $validation[$elementId] = "key_error";
        $this->isValid = false;
      } else {
        $elementValue = $values[$elementId];
        $validation[$elementId] = $element->validate($elementValue);
        if(!$element->isValid()) {
          $this->isValid = false;
        }
      }
    }
    $this->isValidated = true;
    return $validation;
  }

  function deleteSubmission($formSubmissionId) {
    $dbSchema = $this->connectDB();
    $deleteCondition = array(
      "formSubmissionId" => $formSubmissionId
    );    
    $dbSchema->selectTable("form_submissions")->delete($deleteCondition)->commit();
  }

  function updateSubmission($values, $userId, $formSubmissionId) {
    $validation = $this->validate($values);
    if(!$this->isValid()) {
      return ["validation"=>$validation, "valid"=>false];
    }
    $dbSchema = $this->connectDB();
    $data = array();
    $files = array();
    foreach($this->elements as $element) {
      if($element->component == "FileUploadElement") {
        if($element->storeFile($values, $userId)) {
          $files = $files + $element->data($values);      
        }
      } else {
        $data = $data + $element->data($values);
      }
    }
    $insertAttributes = [
      "data" => json_encode($data, JSON_UNESCAPED_UNICODE),
      "files" => json_encode($files, JSON_UNESCAPED_UNICODE),
    ];    
    $condition = [
      "formSubmissionId" => $formSubmissionId
    ];
    $dbSchema->selectTable("form_submissions")->update($insertAttributes, $condition)->commit();
    return ["validation"=>$validation, "valid"=>true];    
  }

  function submit($values, $userId) {
    $validation = $this->validate($values);
    if(!$this->isValid()) {
      return ["validation"=>$validation, "valid"=>false];
    }
    $dbSchema = $this->connectDB();
    $data = array();
    $files = array();
    foreach($this->elements as $element) {
      if($element->component == "FileUploadElement") {
        if($element->storeFile($values, $userId)) {
          $files = $files + $element->data($values);      
        }      
      } else {
        $data = $data + $element->data($values);
      }
    }  
    $insertAttributes = array(
      "formId" => $this->id,
      "userId" => $userId,
      "data" => json_encode($data, JSON_UNESCAPED_UNICODE),
      "files" => json_encode($files, JSON_UNESCAPED_UNICODE),
    );
    $dbSchema->selectTable("form_submissions")->insert($insertAttributes)->commit();
    return ["validation"=>$validation, "valid"=>true];    
  }

}

class FormElement {

  protected $label;
  protected $required;
  protected $formElementId;
  protected $position;
  public $elementId;
  
  protected $isValid;
  protected $isValidated;

  public $component;
  function validateRequired($value) {
    if(!$value && $this->required) {
      return false;
    } else {
      return true;
    }
  }

}

class FileUploadElement extends FormElement {
  public $path;
  public $fliename;
  function __construct($formElementId, $position, $elementId, $data) {
    $this->component = "FileUploadElement";
    $this->elementId = $elementId;
    $this->label = $data["labelName"];
    $this->required = $data["required"];
    $this->postiion = $position;
    $this->formElementId = $formElementId;
    $this->path = $data["path"];      
  }
  function repr() {
    return array(
      "labelName"=>$this->label,
      "required"=>$this->required,
      "formElementId"=>$this->formElementId,
      "position"=>$this->position,
      "elementId"=>$this->elementId,
      "component"=>$this->component,
      "path"=>$this->path
    );
  }
  function storeFile($values, $userId) {
    if($values[$this->elementId]["error"]!=0) {
      return false;
    }
    $uniqId = substr(md5(uniqid(rand(), true)), 0, 16);
    $this->filename = $userId."_".$this->elementId."_".$uniqId."_".$values[$this->elementId]["name"];
    $path = $this->path;
    if(!move_uploaded_file($values[$this->elementId]["tmp_name"], $path.$this->filename)) {
      return false;
    }
    return true;
  }
  function data($values) {
    if(!$this->filename) {
      return [];
    }
    return ["{$this->elementId}" => $this->filename];
  }
  function isValid() {
    if(!$this->isValidated) {
      throw new Exception("Element not validated.");
    }
    $this->isValidated=false;
    return $this->isValid;
  }

  function validate($value) {
    $this->isValidated=true;
    if($value["error"]==4 && $this->required) {
      $this->isValid=false;
      return "required_error";
    } else if($value["error"]!=0 && $value["error"] !=4) {
      $this->isValid=false;
      return $value["error"];
    } else {
      $this->isValid=true;
      return "valid";
    }
  }  
}

class InputElement extends FormElement {
  private $file = "D:\\inetpub\\MPortal\\src\\validationSettings.json";
  private $validationSettings;  
  private $inputType;
  function __construct($formElementId, $position, $elementId, $data) {
    $this->component = "InputElement";
    $this->elementId = $elementId;
    $this->label = $data["labelName"];
    $this->inputType = $data["inputType"];
    $this->required = $data["required"];
    $this->postiion = $position;
    $this->formElementId = $formElementId;

    $content = file_get_contents($this->file);
    $this->validationSettings = json_decode($content, true)["input_types"][$this->inputType];    
  }

  function data($values) {
    return ["{$this->elementId}" => $values[$this->elementId]];
  }

  function repr() {
    return array(
      "validationSettings"=>$this->validationSettings,
      "labelName"=>$this->label,
      "required"=>$this->required,
      "formElementId"=>$this->formElementId,
      "position"=>$this->position,
      "elementId"=>$this->elementId,
      "inputType"=>$this->inputType,
      "component"=>$this->component
    );
  }

  function isValid() {
    if(!$this->isValidated) {
      throw new Exception("Element not validated.");
    }
    $this->isValidated=false;
    return $this->isValid;
  }

  function validateRegex($value) {
    return preg_match("/{$this->validationSettings["regex"]}/", $value);
  }

  function validate($value) {
    $this->isValidated=true;
    if(!$this->validateRequired($value)) {
      $this->isValid=false;
      return "required_error";
    } else if(!$this->validateRegex($value)) {
      $this->isValid=false;
      return "value_error";
    } else {
      $this->isValid=true;
      return "valid";
    }
  }
}

class SelectionElement extends FormElement {
  private $numOptions;
  private $options;
  function __construct($formElementId, $position, $elementId, $data) {
    $this->component = "InputElement";
    $this->elementId = $elementId;
    $this->label = $data["labelName"];
    $this->required = $data["required"];
    $this->numOptions = $data["numOptions"];
    $this->options = $data["options"];
    $this->postiion = $position;
    $this->formElementId = $formElementId;
  }
  function repr() {
    return array(
      "numOptions"=>$this->numOptions,
      "options"=>$this->options,
      "labelName"=>$this->label,
      "required"=>$this->required,
      "formElementId"=>$this->formElementId,
      "position"=>$this->position,
      "elementId"=>$this->elementId,
      "component"=>$this->component
    );
  }
  function data($values) {
    return ["{$this->elementId}" => $values[$this->elementId]];
  }  
  function isValid() {
    if(!$this->isValidated) {
      throw new Exception("Element not validated.");
    }
    $this->isValidated=false;
    return $this->isValid;
  }

  function validate($value) {
    $this->isValidated=true;
    if(!$this->validateRequired($value)) {
      $this->isValid=false;
      return "required_error";
    } else {
      $this->isValid=true;
      return "valid";
    }
  }  
}

class Submission extends Form {
  private $formSubmissionId;
  // private $formId;
  private $userId;
  private $firstname;
  private $lastname;
  private $mail;
  private $dateOfSubmission;
  private $lastUpdate;
  private $flagColor;
  private $flagText;
  private $replies = array();
  private $data;
  private $files;
  private $anonSubmissionKey;
  
  function __construct($formSubmissionId) {
    $this->formSubmissionId = $formSubmissionId;
  }
  function cache() {
    if(!array_key_exists("cache", $_SESSION)) {
      $_SESSION["cache"] = array();
    }
    $elements = array();
    foreach($this->elements as $element) {
      array_push($elements, $element->repr());
    }
    $_SESSION["cache"]["submission_{$this->formSubmissionId}"] = array(
      "userId" => $this->userId,
      "firstname" => $this->firstname,
      "lastname" => $this->lastname,
      "mail" => $this->mail,
      "data" => $this->data,
      "dateOfSubmission" => $this->dateOfSubmission,
      "files" => $this->files,
      "flagColor" => $this->flagColor,
      "flagText" => $this->flagText,
      "anonSubmissionKey" => $this->anonSubmissionKey,
      "lastUpdate" => $this->lastUpdate
    ); 
  }
  function dbBuild() {
    $colTypePairs = array(
      "data" => "JSON",
      "files" => "JSON",
      "dateOfSubmission" => "date",
      "formName" => "once",
      "lastUpdate" => "date"
    );
    $conditions = array(
      "formSubmissionId" => $formSubmissionId,
      "formId" => $this->id
    );    
    $submission = $dbSchema->selectTable("form_submissions")->innerJoin("userId", "users", "userId", array("firstname", "lastname", "mail", "userId"))->select("formSubmissionId", "data", "dateOfSubmission", "files", "submission_flag", "flag_hover_text", "anonSubmissionKey", "lastUpdate")->conditions($conditions)->getAll($colTypePairs)[0];
    if(!$submission){
      return false;
    }
    $this->userId = $submission["userId"];
    $this->firstname = $submission["firstname"];
    $this->lastname = $submission["lastname"];
    $this->mail = $submission["mail"];
    $this->data = $submission["data"];
    $this->dateOfSubmission = $submission["dateOfSubmission"];
    $this->files = $submission["files"];
    $this->flagColor = $submission["submission_flag"];
    $this->flagText = $submission["flag_hover_text"];
    $this->anonSubmissionKey = $submission["anonSubmissionKey"];
    $this->lastUpdate = $submission["lastUpdate"];
    return true;
  }
}
class SubmissionReply {
  private $submissionReplyId;
  private $formId;
  private $formSubmissionId;
  private $replyMessage;
  private $replyDate;
  private $userId;
  private $file;
}
// $ch = new FormCacheHandler();
// $ch->clear();
// $form = new Form(88);
// $form->clear();
// print_r($form->validate([]));
?>
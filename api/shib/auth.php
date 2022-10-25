<?php

function getUserInformation() {
    $ADDITONAL_SHIBBOLETH_ATTRIBUTE_NAMES = [
      "lastname",
      "firstname",
      "mail",
      "memberOf",
      "matriculationNumber",
      "fieldOfStudy",
      "fieldOfStudyId",
      "fieldOfStudyText",
      "degree",
      "degreeText"
    ];
    $user_information = [];
    if(array_key_exists("shib_id", $_SERVER) && $_SERVER["shib_id"]){
      $user_information["shib_id"] = explode("@", $_SERVER["shib_id"])[0];
      $user_information["affiliation"] = explode(";", $_SERVER["affiliation"]);
      foreach($ADDITONAL_SHIBBOLETH_ATTRIBUTE_NAMES as $attribute) {
        if(array_key_exists($attribute, $_SERVER)) {
          $user_information[$attribute] = $_SERVER[$attribute];
        }
      }
    }
    return $user_information;
}

echo "<pre>";
print_r(getUserInformation());
echo "</pre>";
echo "<pre>";
print_r($_SERVER);
echo "</pre>";
?>

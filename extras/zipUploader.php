<?php

require_once "database.php";
ini_set('max_execution_time', 300);


function encodeToUtf8($string) {
    //return mb_convert_encoding($string, "UTF-8", mb_detect_encoding($string, mb_list_encodings(), true));
    return $string;
}

function uploadZips() {
    $file = fopen('extras/smallGlobalZips.csv', 'r');
    $con = Connection::connect();
    while (($line = fgetcsv($file)) !== FALSE) {
        $stmt = $con->prepare("insert into ZipcodeLocator (`countryCode`, `postCode`, `city`, `state`, `stateCode`, `latitude`, `longitude`, `acc`) values (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bindValue(1, $line[0], PDO::PARAM_STR);
        $stmt->bindValue(2, $line[1], PDO::PARAM_STR);
        $stmt->bindValue(3, encodeToUtf8($line[2]), PDO::PARAM_STR);
        $stmt->bindValue(4, encodeToUtf8($line[3]), PDO::PARAM_STR);
        $stmt->bindValue(5, encodeToUtf8($line[4]), PDO::PARAM_STR);
        $stmt->bindValue(6, $line[5], PDO::PARAM_STR);
        $stmt->bindValue(7, $line[6], PDO::PARAM_STR);
        $stmt->bindValue(8, $line[7], PDO::PARAM_INT);
        $stmt->execute();
        //print_r($line);
        //echo "\n";
    }
    fclose($file);
    $con = null;


}


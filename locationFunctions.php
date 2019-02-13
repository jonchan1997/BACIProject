<?php
include_once "database.php";
/*
    This should be for all functions that are about a physical location, so addresses, countries, states, etc.
    This includes adding, editing, or "deleting" a state or country, calculating distance, retrieving addresses,
    states, or countries, etc.
*/


/* NOTE: User-specific functions section */


function getAddressIDFromAccount($account_id) {

    $con = Connection::connect();
    $stmt = $con->prepare("SELECT address_ID FROM `Address History` where account_ID = ? and isnull(end) ");
    $stmt->bindValue(1, $account_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $con = null;
    $stmt = null;

    return $result['address_ID'];
}

function updateUserAddress($account_id, $address) {
    registerNewAddress($address);

    $address_id = getAddressID($address);

    $con = Connection::connect();
    $stmt = $con->prepare("insert into `Address History` (`address_ID`, `account_ID`, `start`) values (?, ?, CURRENT_TIMESTAMP)");
    $stmt->bindValue(1, $address_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $account_id, PDO::PARAM_INT);
    $stmt->execute();
    $con = null;
}

function getApproximateLocation($account_id) {
    $con = Connection::connect();
    $stmt = $con->prepare("select address_ID from `Address History` where account_ID = ? and isnull(end)");
    $stmt->bindValue(1, $account_id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($row == null){
        return "Somewhere over the rainbow";
    }

    $address_id = $row['address_ID'];
    $stmt = $con->prepare("select * from `Addresses` where address_ID = ?");
    $stmt->bindValue(1, $address_id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($row == null){
        return "Somewhere over the rainbow";
    }
    $city = $row['city'];

    $stmt = $con->prepare("select state_name from `States` where state_id = ?");
    $stmt->bindValue(1, $row['state'], PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $con = null;

    return  $city . ", " . $row['state_name'];
}

function getAddressLine1($account_id) {
    $address_id = getAddressIDFromAccount($account_id);

    $con = Connection::connect();
    $stmt = $con->prepare("SELECT street_address FROM `Addresses` where address_ID = ?");
    $stmt->bindValue(1, $address_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $con = null;
    $stmt = null;

    return $result['street_address'];
}

function getAddressLine2($account_id) {
    $address_id = getAddressIDFromAccount($account_id);

    $con = Connection::connect();
    $stmt = $con->prepare("SELECT street_address2 FROM `Addresses` where address_ID = ?");
    $stmt->bindValue(1, $address_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $con = null;
    $stmt = null;

    return $result['street_address2'];
}

function getCity($account_id) {
    $address_id = getAddressIDFromAccount($account_id);

    $con = Connection::connect();
    $stmt = $con->prepare("SELECT city FROM `Addresses` where address_ID = ?");
    $stmt->bindValue(1, $address_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $con = null;
    $stmt = null;

    return $result['city'];
}

function getPostCode($account_id) {
    $address_id = getAddressIDFromAccount($account_id);

    $con = Connection::connect();
    $stmt = $con->prepare("SELECT post_code FROM `Addresses` where address_ID = ?");
    $stmt->bindValue(1, $address_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $con = null;
    $stmt = null;

    return $result['post_code'];
}

function getCountry($account_id) {
    $address_id = getAddressIDFromAccount($account_id);

    $con = Connection::connect();
    $stmt = $con->prepare("SELECT country_ID FROM `Addresses` where address_ID = ?");
    $stmt->bindValue(1, $address_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $country_ID = $result['country_ID'];

    $stmt = $con->prepare("SELECT country FROM `Countries` where country_ID = ?");
    $stmt->bindValue(1, $country_ID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $con = null;
    $stmt = null;

    return $result['country'];
}


/* NOTE: General Address functions section */


function registerNewAddress($address) {
    $con = Connection::connect();
    $stmt = $con->prepare("insert into Addresses (country_ID, state, city, post_code, street_address, street_address2) values (?, ?, ?, ?, ?, ?)");
    $stmt->bindValue(1, $address->country, PDO::PARAM_INT);
    $stmt->bindValue(2, $address->state, PDO::PARAM_STR);
    $stmt->bindValue(3, $address->city, PDO::PARAM_STR);
    $stmt->bindValue(4, $address->postcode, PDO::PARAM_STR);
    $stmt->bindValue(5, $address->street, PDO::PARAM_STR);
    $stmt->bindValue(6, $address->street2, PDO::PARAM_STR);
    $stmt->execute();
    $con = null;
}

function getAddressID($address) {
    $con = Connection::connect();
    $stmt = $con->prepare("select address_ID from Addresses where street_address = ? and street_address2 = ? and post_code = ? and city = ? and country_id = ? and state = ?");
    $stmt->bindValue(1, $address->street, PDO::PARAM_STR);
    $stmt->bindValue(2, $address->street2, PDO::PARAM_STR);
    $stmt->bindValue(3, $address->postcode, PDO::PARAM_STR);
    $stmt->bindValue(4, $address->city, PDO::PARAM_STR);
    $stmt->bindValue(5, $address->country, PDO::PARAM_INT);
    $stmt->bindValue(6, $address->state, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $con = null;
    return $row['address_ID'];
}




/* NOTE: Country functions section */

/* NOTE: the function getCountry($account_id) is located in the "User-specific functions" section */


function getCountryID($account_id) {
    $address_id = getAddressIDFromAccount($account_id);

    $con = Connection::connect();
    $stmt = $con->prepare("SELECT country_ID FROM `Addresses` where address_ID = ?");
    $stmt->bindValue(1, $address_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $con = null;
    $stmt = null;

    return $result['country_ID'];
}

function listCountries($account_id = 0) {
    $con = Connection::connect();
    $stmt = $con->prepare("SELECT country, country_ID FROM `Countries` WHERE enabled = 1");
    $stmt->execute();
    $list = $stmt->fetchAll();
    $con = null;
    $html = "";

    if ($account_id != 0) {
        $selected = getCountryID($account_id);
        foreach ($list as $option) {
            if ($option["country_ID"] == $selected) {
                $html = $html . '<option selected value="' . $option["country_ID"] . '"> ' . $option["country"] . ' </option> ';
            } else {
                $html = $html . '<option value="' . $option["country_ID"] . '"> ' . $option["country"] . ' </option> ';
            }
        }
    } else {
        foreach ($list as $option) {
            $html = $html . '<option value="' . $option["country_ID"] . '"> ' . $option["country"] . ' </option> ';
        }
    }

    return $html;
}

// This function will add a new country to the database, but will check if it's already in the database first
function addCountry($countryName){
    $con = Connection::connect();

	if($con == null){
        $report = new Report("Error connecting to database", "We were unable to connect to the database at this time", "addCountry", FALSE);
        return $report;
    }

    if($countryName == ""){
        $report = new Report("Invalid Country Name", "Please enter a country name", "addCountry", FALSE);
        return $report;
    }

    if(!ctype_alpha ($countryName)){
        $report = new Report("Invalid Country Name", "Please enter a valid country name", "addCountry", FALSE);
        return $report;
    }

    $stmt = $con->prepare("SELECT * FROM `Countries` WHERE country = ?");
    $stmt->bindValue(1, $countryName, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if($result != null){	//if there is already a country with the same name in the database
        $enabled = $result['enabled'];
        if($enabled == "0"){
            $stmt = $con->prepare("UPDATE `Countries` SET enabled = 1 WHERE country = ?");
            $stmt->bindValue(1, $countryName, PDO::PARAM_STR);
            $success = $stmt->execute();

			if($success){
                $report = new Report("Success!", "The country was successfully re-enabled", "addCountry", TRUE);
            }
            else{
            	$report = new Report("Failed to re-enable country", "There was an error while trying to re-enable the country", "addCountry", FALSE);
            }
        }
		else{
			$report = new Report("Redundant Entry", "There is already a country with that name", "addCountry", FALSE);
		}
        $con = null;
        return $report;
    }
    $stmt = null;
    $result = null;

    $stmt = $con->prepare("INSERT INTO `Countries` (country) values (?)");
    $stmt->bindValue(1, $countryName, PDO::PARAM_STR);
    $success = $stmt->execute();

	if($success){
        $report = new Report("Success!", "The new country was successfully inserted into the database", "addCountry", TRUE);
    }
    else{
        $report = new Report("Failed to add new country", "There was an error while trying to create the new country", "addCountry", FALSE);
    }

    $stmt = null;
    $con = null;
    return $report;
}
// This function will edit a pre-existing country name in the database
function editCountry($id, $newName){
    $con = Connection::connect();

	if($con == null){
        $report = new Report("Error connecting to database", "We were unable to connect to the database at this time", "addCountry", FALSE);
        return $report;
    }

    if($newName == ""){
        $report = new Report("Invalid Country Name", "Please enter a name", "addCountry", FALSE);
        return $report;
    }

    if(!ctype_alpha ($newName)){
        $report = new Report("Invalid Country Name", "Please enter a valid country name", "addCountry", FALSE);
        return $report;
    }

    $stmt = $con->prepare("UPDATE `Countries` SET country = ? WHERE country_ID = ?");
    $stmt->bindValue(1, $newName, PDO::PARAM_STR);
    $stmt->bindValue(2, $id, PDO::PARAM_INT);
	$success = $stmt->execute();

    if($success){
        $report = new Report("Success!", "The country name was successfully changed", "addCountry", TRUE);
    }
    else{
        $report = new Report("Failed to edit country", "There was an error while trying to edit the country's name", "addCountry", FALSE);
    }

    $con = null;
    $stmt = null;
    return $report;
}
// This function will delete a country from the database
function deleteCountry($country_ID){
    $con = Connection::connect();

	if($con == null){
        $report = new Report("Error connecting to database", "We were unable to connect to the database at this time", "addCountry", FALSE);
        return $report;
    }

    $stmt = $con->prepare("UPDATE `Countries` SET enabled = 0 WHERE country_ID = ?");
    $stmt->bindValue(1, $country_ID, PDO::PARAM_INT);
    $success = $stmt->execute();

	if($success){
        $report = new Report("Success!", "The country name was successfully disabled", "addCountry", TRUE);
    }
    else{
        $report = new Report("Failed to disable country", "There was an error while trying to disable the country", "addCountry", FALSE);
    }

    $stmt = $con->prepare("UPDATE `States` SET enabled = 0 WHERE country_ID = ?");
    $stmt->bindValue(1, $country_ID, PDO::PARAM_INT);

    $con = null;
    $stmt = null;
    return $report;
}


/* NOTE: State functions section */


function getStateID($account_id) {
    $address_id = getAddressIDFromAccount($account_id);

    $con = Connection::connect();
    $stmt = $con->prepare("SELECT state FROM `Addresses` where address_ID = ?");
    $stmt->bindValue(1, $address_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $con = null;
    $stmt = null;

    return $result['state'];
}

//returns all enabled states in the form of an array, not for displaying.
function getStatesArray(){
    $con = Connection::connect();
    $stmt = $con->prepare("SELECT country_ID, state_name, state_ID FROM States WHERE enabled = 1");
    $stmt->execute();
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $list;
}

//returns all states belonging to the given country, for use in drop-down lists.
function getStatesList($countryID, $account_id = -1){
    $con = Connection::connect();
    $stmt = $con->prepare("SELECT state_name, state_ID FROM States WHERE country_ID = ? AND enabled = 1");
    $stmt->bindValue(1, $countryID, PDO::PARAM_INT);
    $stmt->execute();
    $list = $stmt->fetchAll();
    $con = null;

    if ($account_id == -1) {
        $selected = -1;
    } else {
        $selected = getStateID($account_id);
    }


    $html = "";
    foreach ($list as $option) {
        if ($option["state_ID"] == $selected) {
            $html = $html . '<option selected value="' . $option["state_ID"] . '"> ' . $option["state_name"] . ' </option> ';
        } else {
            $html = $html . '<option value="' . $option["state_ID"] . '"> ' . $option["state_name"] . ' </option> ';
        }
    }
    return $html;
}

//this function will add a new state to the database, and associate it with the given country
function addState($countryID, $stateName){
    $con = Connection::connect();

	if($con == null){
        $report = new Report("Error connecting to database", "We were unable to connect to the database at this time", "addState", FALSE);
        return $report;
    }

    if($stateName == ""){
        $report = new Report("Invalid State Name", "Please enter a state name", "addState", FALSE);
        return $report;
    }

    if(!ctype_alpha ($stateName)){
        $report = new Report("Invalid State Name", "Please enter a valid state name", "addState", FALSE);
        return $report;
    }

    $stmt = $con->prepare("SELECT * FROM States WHERE state_name = ? AND country_ID = ?");
    $stmt->bindValue(1, $stateName, PDO::PARAM_STR);
    $stmt->bindValue(2, $countryID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if($result != null){	//if there is already a country with the same name in the database
        if($result['enabled'] == "0"){
            $stmt = $con->prepare("UPDATE States SET enabled = 1 WHERE state_name = ?");
            $stmt->bindValue(1, $stateName, PDO::PARAM_STR);
            $success = $stmt->execute();

			if($success){
				$report = new Report("Success!", "The state was successfully re-enabled", "addState", TRUE);
			}
			else{
				$report = new Report("Failed", "There was an error while trying to add the new state to the database", "addState", FALSE);
			}
        }
		else{
			$report = new Report("Redundant Request", "The there is already a state with that name in that country in the database", "addState", FALSE);
		}
        $con = null;
        return $report;
    }
    $stmt = null;
    $result = null;

    $stmt = $con->prepare("INSERT INTO States (country_ID, state_name, state_ID) values (?, ?, DEFAULT)");
    $stmt->bindValue(1, $countryID, PDO::PARAM_INT);
    $stmt->bindValue(2, $stateName, PDO::PARAM_STR);
    $success = $stmt->execute();

	if($success){
		$report = new Report("Success!", "The new state was successfully inserted into the database", "addState", TRUE);
	}
	else{
		$report = new Report("Failed", "There was an error while trying to add the new state to the database", "addState", FALSE);
	}

    $stmt = null;
    $con = null;
    return $report;
}
// This function will edit a pre-existing state name in the database
function editState($newName, $ID){
    $con = Connection::connect();

	if($con == null){
        $report = new Report("Error connecting to database", "We were unable to connect to the database at this time", "addState", FALSE);
        return $report;
    }

    if($newName == ""){
        $report = new Report("Invalid State Name", "Please enter a state name", "addState", FALSE);
        return $report;
    }

    if(!ctype_alpha ($newName)){
        $report = new Report("Invalid State Name", "Please enter a valid state name", "addState", FALSE);
        return $report;
    }

    $stmt = $con->prepare("UPDATE States SET state_name = ? WHERE state_ID = ?");
    $stmt->bindValue(1, $newName, PDO::PARAM_STR);
    $stmt->bindValue(2, $ID, PDO::PARAM_INT);
    $success = $stmt->execute();

	if($success){
		$report = new Report("Success!", "The state was successfully edited", "addState", TRUE);
	}
	else{
		$report = new Report("Failed", "There was an error while trying to edit the state", "addState", FALSE);
	}

    $con = null;
    $stmt = null;
    return $report;
}
// This function will delete a state from the database
function deleteState($ID){
    $con = Connection::connect();

	if($con == null){
        $report = new Report("Error connecting to database", "We were unable to connect to the database at this time", "addState", FALSE);
        return $report;
    }

    $stmt = $con->prepare("UPDATE `States` SET enabled = 0 WHERE state_ID = ?");
    $stmt->bindValue(1, $ID, PDO::PARAM_INT);
    $success = $stmt->execute();

	if($success){
		$report = new Report("Success!", "The state was successfully disabled", "addState", TRUE);
	}
	else{
		$report = new Report("Failed", "There was an error while trying to disable the state", "addState", FALSE);
	}

    $con = null;
    $stmt = null;
    return $report;
}

?>

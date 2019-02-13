<?php
require_once "database.php";
require_once "session.php";

if(!isset($_REQUEST["action"])){
    echo "";
    die();
}

if($_REQUEST["action"] == "refreshState"){
    if(!isset($_REQUEST["country"])){
        header("Location:index.php");
    }
    $countryID = $_REQUEST["country"];
    $options = getStatesList($countryID);

    echo $options;
}

if($_REQUEST["action"] == "getDegrees"){
    echo listDegreeTypes();
}

if($_REQUEST['action'] == "adminStartPair"){
    $_SESSION['pair_user'] = $_SESSION["profile_ID"];
    echo formatAdminPairingBox();
    die();
}

if($_REQUEST['action'] == "adminFinishPair"){
    $user1 = $_SESSION['pair_user'];
    $user2 = $_SESSION["profile_ID"];
    unset($_SESSION['pair_user']);
    echo "mentor=" . getUsernameFromAccountID($user1) . "&mentee=" . getUsernameFromAccountID($user2) . "&match=";
    die();
}

if($_REQUEST['action'] == "adminClearPair"){
    unset($_SESSION['pair_user']);
    echo formatAdminPairingBox();
    die();
}
?>

<?php
require_once "session.php";
require_once "database.php";
require_once "card.php";

if (isset($_POST['register'])) {
    $error = false;
    $msg = "";

    $requiredPOSTFieldNames = array('password', 'confirmedPassword', 'username', 'firstName', 'lastName', 'email', 'gender', 'phoneNumber', 'status', 'preference', 'state', 'country', 'numDegs', 'numJobs', 'dob');
    $optionalPOSTFieldNames = array('middleName', 'street', 'street2', 'city', 'postcode', 'facebook', 'twitter', 'linkedin');

    foreach ($requiredPOSTFieldNames as $req) {
        if (isset($_REQUEST[$req])) {
            $_SESSION[$req] = Input::str($_POST[$req]);
        } else {
            $error = true;
        }
    }

    foreach ($optionalPOSTFieldNames as $req) {
        $_SESSION[$req] = (isset($_REQUEST[$req]) ? $_SESSION[$req] = Input::str($_POST[$req]) : $_SESSION[$req] = "");
    }

    // Collect Education and Work Histories
    $degree = array();
    for ($degreeNum = 0; $degreeNum < $_SESSION['numDegs']; $degreeNum++) {
        foreach (array('schoolName_' . $degreeNum, 'degreeType_' . $degreeNum, 'major_' . $degreeNum, 'enrollmentYear_' . $degreeNum, 'gradYear_' . $degreeNum) as $req) {
            $_SESSION[$req] = (isset($_REQUEST[$req]) ? $_SESSION[$req] = Input::str($_POST[$req]) : $_SESSION[$req] = "");
        }
        if ($_SESSION['schoolName_' . $degreeNum] != "" && $_SESSION['major_' . $degreeNum] != "") {
            $degree[$degreeNum] = new EducationHistoryEntry($_SESSION['schoolName_' . $degreeNum], $_SESSION['degreeType_' . $degreeNum], $_SESSION['major_' . $degreeNum], $_SESSION['enrollmentYear_' . $degreeNum], $_SESSION['gradYear_' . $degreeNum]);
        }
    }

    $work = array();
    for ($jobNum = 0; $jobNum < $_SESSION['numJobs']; $jobNum++) {
        foreach (array('employerName_' . $jobNum, 'jobTitle_' . $jobNum, 'startYear_' . $jobNum, 'endYear_' . $jobNum) as $req) {
            $_SESSION[$req] = (isset($_REQUEST[$req]) ? $_SESSION[$req] = Input::str($_POST[$req]) : $_SESSION[$req] = "");
        }
        if ($_SESSION['employerName_' . $jobNum] != "" && $_SESSION['jobTitle_' . $jobNum] != "") {
            $work[$jobNum] = new WorkHistoryEntry($_SESSION['employerName_' . $jobNum], $_SESSION['jobTitle_' . $jobNum], $_SESSION['startYear_' . $jobNum], $_SESSION['endYear_' . $jobNum]);
        }
    }

    // handle files
    $picturePath = $_FILES['profile']['tmp_name'];
    $resumePath = $_FILES['resume']['tmp_name'];

    // verify password
    if ($_SESSION['password'] != $_SESSION['confirmedPassword']) {
        $error = true;
        $msg .= "\nPasswords do not match.";
    } elseif (!(preg_match('/[A-Za-z]/', $_SESSION['password']) && preg_match('/[0-9]/', $_SESSION['password']))) {
        $error = true;
        $msg .= "\nPassword must contain a capital letter and a number.";
    } elseif (strlen($_SESSION['password']) < 12) {
        $error = true;
        $msg .= "\nPassword must be 12 or more characters.";
    }

    // verify email format and if we already have that email in the server
    $sanitized_email = Input::email($_SESSION['email']);
    if (!$sanitized_email) {
        $error = true;
        $msg .= "\nEmail is not a valid address.";
    }
    $_SESSION['email'] = $sanitized_email;

    $con = Connection::connect();
    $stmt = $con->prepare("select account_ID from Information where email_address = ?");
    $stmt->bindValue(1, $_SESSION['email'], PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($row != null) {
        $error = true;
        $msg .= "\nAn account with this email already exists.";
    }
    $con = null;

    // check that username is unique
    $con = Connection::connect();
    $stmt = $con->prepare("select account_ID from Account where username = ?");
    $stmt->bindValue(1, $_SESSION['username'], PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($row != null) {
        $error = true;
        $msg .= "\nAn account with this username already exists.";
    }
    $con = null;

    // check that gender is in range
    if (!is_numeric($_SESSION['gender']) || $_SESSION['gender'] > 2 || $_SESSION['gender'] < 0) {
        $error = true;
        $msg .= "\nInvalid gender.";
    }

    // check that status is in range
    if (!is_numeric($_SESSION['status']) || ($_SESSION['status'] != 0 && $_SESSION['status'] != 1)) {
        $error = true;
        $msg .= "\nInvalid status.";
    } else if ($_SESSION['status'] == 1 && count($work) == 0) {
        $error = true;
        $msg .= "\nWorking Professionals must enter at least one job.";
    }

    // check that preference is in range
    if (!is_numeric($_SESSION['preference']) || $_SESSION['preference'] > 2 || $_SESSION['preference'] < 0) {
        $error = true;
        $msg .= "\nInvalid preference.";
    }

    // check that country is a number that correlates to a country in the db
    if (!is_numeric($_SESSION['country'])) {
        $error = true;
        $msg .= "\nInvalid country.";
    }
    $con = Connection::connect();
    $stmt = $con->prepare("select country from Countries where country_ID = ?");
    $stmt->bindValue(1, $_SESSION['country'], PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if(empty($row)) {
        $error = true;
        $msg .= "\nInvalid country.";
    }
    $con = null;

    // check that state is a number that correlates to a state and a country in the db
    if (!is_numeric($_SESSION['state'])) {
        $error = true;
        $msg .= "\nInvalid state.";
    }
    $con = Connection::connect();
    $stmt = $con->prepare("select state_name from States where state_ID = ? and country_ID = ?");
    $stmt->bindValue(1, $_SESSION['state'], PDO::PARAM_INT);
    $stmt->bindValue(2, $_SESSION['country'], PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if(empty($row)) {
        $error = true;
        $msg .= "\nInvalid state.";
    }
    $con = null;

    if ($error == false) {
        $user = new User($_SESSION['username'], $_SESSION['password'], $_SESSION['firstName'], $_SESSION['middleName'], $_SESSION['lastName'], $_SESSION['email'], $_SESSION['gender'], $_SESSION['phoneNumber'], $_SESSION['status'], $_SESSION['preference'], $_SESSION['dob']);
        $address = new Address($_SESSION['street'], $_SESSION['street2'], $_SESSION['city'], $_SESSION['postcode'], $_SESSION['state'], $_SESSION['country']);
        registerUser($user, $address, $degree, $work, $picturePath, $resumePath);
        //header("Location: created.php");
        $report = new Report("Registration Success", "Check email to confirm account", "", FALSE);
        $_SESSION['title'] = $report->title;
        $_SESSION['msg'] = $report->msg;
        $_SESSION['nextModal'] = $report->nextModal;
        $_SESSION['success'] = $report->success;
        $_SESSION['inputs'] = $report->inputs;
        header("Location: index.php");
        die();
    } else {
        $report = new Report("Error in Registration!", $msg, "registerModal", FALSE);
        $_SESSION['title'] = $report->title;
        $_SESSION['msg'] = $report->msg;
        $_SESSION['nextModal'] = $report->nextModal;
        $_SESSION['success'] = $report->success;
        $_SESSION['inputs'] = $report->inputs;
        header("Location: index.php");
        die();
    }
}
if(isset($_REQUEST['match']) && isset($_REQUEST['mentor']) && isset($_REQUEST['mentee'])) {
	if ($type < 2) {
		header("Location: index.php");
		die;
	} elseif ($type > 2) {

		$con = Connection::connect();
		$stmt = $con->prepare("SELECT `account_ID` FROM Account WHERE username = ?");
		$stmt->bindValue(1, $_REQUEST['mentor'], PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch();
		if ($row == null) {
            $report = new Report("Manual Match Error!", "An invalid user was specified.", "matchModal", FALSE);
            $_SESSION['title'] = $report->title;
            $_SESSION['msg'] = $report->msg;
            $_SESSION['nextModal'] = $report->nextModal;
            $_SESSION['success'] = $report->success;
            $_SESSION['inputs'] = $report->inputs;
			header("Location: index.php");
			die();
		}
		$mentorID = $row['account_ID'];

		$stmt = $con->prepare("SELECT `account_ID` FROM Account WHERE username = ?");
		$stmt->bindValue(1, $_REQUEST['mentee'], PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch();
		if ($row == null) {
            $report = new Report("Manual Match Error!", "An invalid user was specified.", "matchModal", FALSE);
            $_SESSION['title'] = $report->title;
            $_SESSION['msg'] = $report->msg;
            $_SESSION['nextModal'] = $report->nextModal;
            $_SESSION['success'] = $report->success;
            $_SESSION['inputs'] = $report->inputs;
			header("Location: index.php");
			die();
		}
		$menteeID = $row['account_ID'];

		proposeMentorship($mentorID, $menteeID, $_SESSION['account_ID']);
        $report = new Report("Manual Match Completed", getName($mentorID) . " and " . getName($menteeID) . " are now matched.", "matchModal", TRUE);
        $_SESSION['title'] = $report->title;
        $_SESSION['msg'] = $report->msg;
        $_SESSION['nextModal'] = $report->nextModal;
        $_SESSION['success'] = $report->success;
        $_SESSION['inputs'] = $report->inputs;
        header("Location: index.php");
		die;
	}
}
?>
<!-- template from: https://www.w3schools.com/w3css/w3css_templates.asp -->
<!DOCTYPE html>
<html>
<head>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BAConnect Home</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="js/registration.js"></script>
    <script src="js/closeModals.js"></script>
    <script src="js/cardHandler.js"></script>
</head>

<body class="w3-light-grey" onload="init();">
<!-- Navbar -->
<?php include "header.php"; ?>
<!-- Page content -->
<div id="cardDisplay" class="flex-container" style="display: flex; flex-wrap: wrap; justify-content: center; align-items: stretch; align-content: flex-start;">

</div>
</body>
<script src="js/search.js"></script>
<script>
    $(window).on("load", function(){
        continuallyLoadCards(30, -1);
        $(window).on("scroll", function(){
            if (($(window).scrollTop() - ($(document).height() - $(window).height()) <= 5) && ($(window).scrollTop() - ($(document).height() - $(window).height()) >= -5)) {
                let term = document.getElementById("searchBox").value;
                if (term == "") {
                    continuallyLoadCards(10, -1);
                } else {
                    searchCards(10, false, -1);
                }
            }
        });
    });
</script>
</html>

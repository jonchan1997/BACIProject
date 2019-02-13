<?php
require_once "database.php";
require_once "session.php";

if (!isset($_SESSION['email']) || !isset($_SESSION['code'])) {
    if(!isset($_SESSION['inputs'])){
        $_SESSION['title'] = "Error";
        $_SESSION['msg'] = "email or code were not set";
        $_SESSION['nextModal'] = "changePassModal";
        $_SESSION['success'] = TRUE;
        $_SESSION['inputs'] = null;
        header("Location: index.php");
        die();
    }
    else{
        $_SESSION['email'] = $_SESSION['inputs'][1];
        $_SESSION['code'] = $_SESSION['inputs'][2];

        unset($_SESSION['inputs']);
    }
}

if (isset($_POST['submit'])) {
    $pw_1 = filter_input(INPUT_POST, "password_1");
    $pw_2 = filter_input(INPUT_POST, "password_2");
    $email = filter_input(INPUT_POST, "email");

    //$email = urlencode($email);

    if (($pw_1 == $pw_2) && (strcasecmp($email, $_SESSION['email']) === 0)) {
        $report = changePassword($_SESSION['email'], $_SESSION['code'], $pw_1);
        unset($_SESSION['email']);
        unset($_SESSION['code']);
        $_SESSION['title'] = $report->title;
        $_SESSION['msg'] = $report->msg;
        $_SESSION['nextModal'] = $report->nextModal;
        $_SESSION['success'] = $report->success;
        $_SESSION['inputs'] = $report->inputs;
        header("Location: index.php");
        die();
    } else {
        $sessionEmail = $_SESSION['email'];
        $code = $_SESSION['code'];

        $_SESSION['title'] = "Error";
        $_SESSION['nextModal'] = "changePassModal";
        $_SESSION['success'] = FALSE;
        $_SESSION['inputs'] = array($sessionEmail, $code);

        if($pw_1 != $pw_2){
            $_SESSION['msg'] = "Passwords were not the same";
        } else if(strcasecmp($email, $_SESSION['email']) != 0) {
            $_SESSION['msg'] = "Incorrect Email: " . $email . " does not match " . $_SESSION['email'];
        } else {
            $_SESSION['msg'] = "An unknown error has occured";
        }
        header("Location: changePassword.php");
        die();
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BAConnect Home</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="js/registration.js"></script>
    <script src="js/closeModals.js"></script>
</head>

<body class="w3-light-grey" onload="document.getElementById('changePassModal').style.display='block';">
<!-- Navbar -->
<?php include "header.php"; ?>
<!-- Page content -->
<div id="changePassModal" class="w3-modal">
    <div class="w3-modal-content w3-animate-top w3-card-4">
        <header class="w3-container w3-lime w3-center w3-padding-32">
            <span onclick="document.getElementById('changePassModal').style.display='none'" class="w3-button w3-lime w3-xlarge w3-display-topright">×</span>
            <h2 class="w3-wide">
                <i class="w3-margin-right"></i>Change Password
            </h2>
        </header>
        <form method="post" action="changePassword.php" class="w3-container">

            <p>
                <label>
                    <i class="fa fa-lock"></i> Email
                </label>
            </p>
            <input class="w3-input w3-border" type="text" placeholder="" name="email" id="email">

            <p>
                <label>
                    <i class="fa fa-lock"></i> New Password
                </label>
            </p>
            <input class="w3-input w3-border" type="password" placeholder="" name="password_1" id="password_1">

            <p>
                <label>
                    <i class="fa fa-lock"></i> New Password again
                </label>
            </p>
            <input class="w3-input w3-border" type="password" placeholder="" name="password_2" id="password_2">

            <button class="w3-button w3-block w3-lime w3-padding-16 w3-section w3-right" type="submit" name="submit" id="submit">Change Password
                <i class="fa fa-check"></i>
            </button>
            <button type="button" class="w3-button w3-red w3-section" onclick="document.getElementById('changePassModal').style.display='none'">Close
                <i class="fa fa-remove"></i>
            </button>
        </form>
    </div>
</div>


<!-- End Page Content -->
</body>
</html>

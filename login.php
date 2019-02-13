<?php
require_once "session.php";

if (isset($_POST['login'])) {
    require_once "database.php";

    $username = $_POST['username'];
    $password = $_POST['password'];

    $report = login($username, $password);

    if ($report->success) {
        $account_id = getAccountIDFromUsername($username);
        $_SESSION['account_ID'] = $account_id;
        $_SESSION['type'] = getAccountTypeFromAccountID($account_id);

        unset($_SESSION['title']);
        unset($_SESSION['msg']);
        unset($_SESSION['nextModal']);
        unset($_SESSION['success']);
        unset($_SESSION['inputs']);
    }
    else{
        $_SESSION['title'] = $report->title;
        $_SESSION['msg'] = $report->msg;
        $_SESSION['nextModal'] = $report->nextModal;
        $_SESSION['success'] = $report->success;
        $_SESSION['inputs'] = $report->inputs;
    }
    header('Location: index.php');
    die;
}
?>

<div id="loginModal" class="w3-modal">
    <div class="w3-modal-content w3-animate-top w3-card-4">
        <header class="w3-container w3-lime w3-center w3-padding-32">
            <span onclick="document.getElementById('loginModal').style.display='none'" class="w3-button w3-lime w3-xlarge w3-display-topright">×</span>
            <h2 class="w3-wide">
                <i class="w3-margin-right"></i>Log In
            </h2>
        </header>
        <form method="post" action="login.php" class="w3-container">
            <p>
                <label>
                    <i class="fa fa-user"></i> Username
                </label>
            </p>
            <input class="w3-input w3-border" type="text" placeholder="" name="username" id="username">
            <p>
                <label>
                    <i class="fa fa-lock"></i> Password
                </label>
            </p>
            <input class="w3-input w3-border" type="password" placeholder="" name="password" id="password">
            <button class="w3-button w3-block w3-lime w3-padding-16 w3-section w3-right" type="submit" name="login" id="login">Log In
                <i class="fa fa-check"></i>
            </button>
            <button type="button" class="w3-button w3-red w3-section" onclick="document.getElementById('loginModal').style.display='none'">Close
                <i class="fa fa-remove"></i>
            </button>
            <p class="w3-right">Need an
                <a href="#" class="w3-text-blue" onclick="document.getElementById('registerModal').style.display='block'">account?</a>
            </p>
        </form>
    </div>
</div>

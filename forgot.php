<?php
require_once "database.php";
require_once "session.php";

if(isset($_POST["security"]) && isset($_POST["email"])){

    $email = Input::email($_POST["email"]);
    $con = Connection::connect();
    $stmt = $con->prepare("SELECT * FROM RecoveryQuestions WHERE account_ID = ?");
    $stmt->bindValue(1, getAccountIDFromEmail($email), PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll();

    if (count($result) > 0) {
        $_SESSION['recovery_email'] = $email;
        $report = new Report("Security Audit", "Please answer your security questions to reset the account for " . $email, "securityModal", true);
        $_SESSION['title'] = $report->title;
        $_SESSION['msg'] = $report->msg;
        $_SESSION['nextModal'] = $report->nextModal;
        $_SESSION['success'] = $report->success;
        $_SESSION['inputs'] = $report->inputs;
        header('Location: index.php');
        die;
    } else {
        $report = resetPassword($email);
        $_SESSION['title'] = $report->title;
        $_SESSION['msg'] = $report->msg;
        $_SESSION['nextModal'] = $report->nextModal;
        $_SESSION['success'] = $report->success;
        $_SESSION['inputs'] = $report->inputs;
        unset($_SESSION['recovery_email']);
        header("Location: index.php");
        die();
    }
}
?>

<div id="forgotModal" class="w3-modal">
    <div class="w3-modal-content w3-animate-top w3-card-4">
        <header class="w3-container w3-lime w3-center w3-padding-32">
            <span onclick="document.getElementById('forgotModal').style.display='none'"
                  class="w3-button w3-lime w3-xlarge w3-display-topright">×</span>
            <h2 class="w3-wide"><i class="w3-margin-right"></i>Reset Password </h2>
        </header>
        <form method="post" action="forgot.php" class="w3-container">
            <p>
                <label>
                    <i class="fa fa-user"></i> Email associated with an account
                </label>
            </p>
            <input class="w3-input w3-border" type="text" placeholder="" name="email" id="email">
            <button class="w3-button w3-block w3-lime w3-padding-16 w3-section w3-right" type="submit" name="security">
                Reset Password
                <i class="fa fa-check"></i>
            </button>
            <button type="button" class="w3-button w3-red w3-section"
                    onclick="document.getElementById('forgotModal').style.display='none'">Close
                <i class="fa fa-remove"></i>
            </button>
            <p class="w3-right">Need an
                <a href="#" class="w3-text-blue"
                   onclick="document.getElementById('registerModal').style.display='block'">account?</a>
            </p>
        </form>
    </div>
</div>

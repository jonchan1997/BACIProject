<?php
require_once "database.php";
require_once "session.php";

$code = filter_input(INPUT_GET, "code");
$email = filter_input(INPUT_GET, "email", FILTER_VALIDATE_EMAIL);
$verifyType = filter_input(INPUT_GET, "type");

if ($code && $email) {
    if ($verifyType == "reg") {
        if (verifyCode($code, $email, 'reg')) {
            $con = Connection::connect();
            $stmt = $con->prepare("select account_ID from Information where email_address = ?");
            $stmt->bindValue(1, $email, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $account_id = $row['account_ID'];

            $stmt = $con->prepare("UPDATE Account SET active = '1' WHERE account_ID = ?");
            $stmt->bindValue(1, $account_id, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $con->prepare("DELETE FROM Registration WHERE account_ID = ?");
            $stmt->bindValue(1, $account_id, PDO::PARAM_INT);
            $stmt->execute();

            $report = new Report("Success", "Your account was successfully activated.", "", TRUE);

            $_SESSION['title'] = $report->title;
            $_SESSION['msg'] = $report->msg;
            $_SESSION['nextModal'] = $report->nextModal;
            $_SESSION['success'] = $report->success;
            $_SESSION['inputs'] = $report->inputs;
            header("Location: index.php");
            die;
        } else {
            $report = new Report("Error", "Your activation code isn't valid. Your account may have already been activated.", "", FALSE);

            $_SESSION['title'] = $report->title;
            $_SESSION['msg'] = $report->msg;
            $_SESSION['nextModal'] = $report->nextModal;
            $_SESSION['success'] = $report->success;
            $_SESSION['inputs'] = $report->inputs;
            header("Location: index.php");
            die;
        }
    } elseif ($verifyType == "reset") {
        if (verifyCode($code, $email, 'reset')) {
            $_SESSION['email'] = $email;
            $_SESSION['code'] = $code;
            header("Location: changePassword.php");
            die();
        } else {
            $report = new Report("Error", "Your activation code isn't valid. Your account may have already been activated.", "", FALSE);

            $_SESSION['title'] = $report->title;
            $_SESSION['msg'] = $report->msg;
            $_SESSION['nextModal'] = $report->nextModal;
            $_SESSION['success'] = $report->success;
            $_SESSION['inputs'] = $report->inputs;
            header("Location: index.php");
            die;
        }
    }
} else {

    $report = new Report("Verification Failed", "Invalid code.", NULL, FALSE);

    $_SESSION['title'] = $report->title;
    $_SESSION['msg'] = $report->msg;
    $_SESSION['nextModal'] = $report->nextModal;
    $_SESSION['success'] = $report->success;
    $_SESSION['inputs'] = $report->inputs;

    header("Location: index.php");
    die();
}

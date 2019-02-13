<?php
require_once "database.php";
require_once "session.php";

function loadOnSecurity($accountID)
{
    $con = Connection::connect();
    $set = 0;
    $question = '';
    $query = "SELECT * FROM RecoveryQuestions WHERE account_ID = '" . $accountID . "' ORDER BY question_Number ASC";
    $statement = $con->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();
    foreach ($result as $row) {
        $question .= '<option value=' . $row['question_Number'] . '>' . $row['question'] . '</option>';
        $set = 1;
    }
    if ($set != 1) {
        $con = null;
        //header('Location: index.php');//skips questions
        return False;
    }
    $con = null;
    return $question;
}//loadOnSecurity

function enableNewSecurity($accountID, $question, $answer)
{// var for new questions and answers
    $con = Connection::connect();
    $isFirst = 0;
    $stmt = $con->prepare("SELECT question_Number FROM RecoveryQuestions WHERE account_ID = '" . $accountID . "' ORDER BY question_Number ASC");
    $stmt->execute();
    $search = $stmt->fetchAll();
    foreach ($search as $row) {
        if ($isFirst < $row['question_Number']) {
            $isFirst = $row['question_Number'];
        }
    }
    if (!$isFirst) {
        $stmt = $con->prepare("insert into `RecoveryQuestions` (`account_ID`, `question_Number`, question, answer) values (?, ?, ?, ?)");
        $stmt->bindValue(1, $accountID, PDO::PARAM_INT);
        $stmt->bindValue(2, 1, PDO::PARAM_INT);
        $stmt->bindValue(3, $question, PDO::PARAM_STR);
        $stmt->bindValue(4, $answer, PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $stmt = $con->prepare("insert into `RecoveryQuestions` (`account_ID`, `question_Number`, question, answer) values (?, ?, ?, ?)");
        $stmt->bindValue(1, $accountID, PDO::PARAM_INT);
        $stmt->bindValue(2, $isFirst + 1, PDO::PARAM_INT);
        $stmt->bindValue(3, $question, PDO::PARAM_STR);
        $stmt->bindValue(4, $answer, PDO::PARAM_STR);
        $stmt->execute();

    }
    $con = null;
    return true;
}//enableNewSecurity
function checkSecurityQ($accountID, $question_Num, $answer)
{
    $con = Connection::connect();
    $stmt = $con->prepare("SELECT answer FROM `RecoveryQuestions` WHERE account_ID = ? AND question_Number = ? ");
    $stmt->bindValue(1, $accountID, PDO::PARAM_INT);
    $stmt->bindValue(2, $question_Num, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (strcasecmp($answer, $row['answer']) === 0) {
        $con = null;
        return true;
    } else {
        $email = getEmail($accountID);
        $code = makeCode($email);
        $stmt = $con->prepare("insert into `Password Recovery` (account_ID, code) values (?, ?)");
        $stmt->bindValue(1, $accountID, PDO::PARAM_INT);
        $stmt->bindValue(2, $code, PDO::PARAM_STR);
        $stmt->execute();
        $con = null;
        return false;
    }
}//end checkSecurity
function getSet($accountID)
{
    $con = Connection::connect();
    $set = 0;
    $question = '';
    $query = "SELECT * FROM RecoveryQuestions WHERE account_ID = '" . $accountID . "' ORDER BY question_Number ASC";
    $statement = $con->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();
    foreach ($result as $row) {
        $question .= '<option value="' . $row['question_Number'] . '">' . $row['question'] . '</option>';
        $set = $set + 1;
    }
    $con = null;
    return $set;
}

if (isset($_SESSION['recovery_email'])) {

    $account_id = getAccountIDFromEmail($_SESSION['recovery_email']);

    $con = Connection::connect();
    $timeout = 1209600;// 2 weeks
    $time = $_SERVER['REQUEST_TIME'];//current
    $ct = 0;
    $stmt = $con->prepare("SELECT request_date FROM `Password Recovery` WHERE account_ID = '" . $account_id . "' ORDER BY request_date ASC");
    $stmt->execute();
    $search = $stmt->fetchAll();
    foreach ($search as $row) {
        $d = new DateTime($row['request_date']);
        if ($time - $d->format('U') <= 1209600) {
            $ct = $ct + 1;
        }
    }
    $con = null;

    if ($ct < 3) {
        //print "<h1><b>" . $_SESSION['email'] . "</b></h1>";
        $countSet = getSet($account_id);
        $answerA = "";
        $answerB = "";
        $answerC = "";
        $questionA = 0;
        $questionB = 0;
        $questionC = 0;
        $continue = 1;
        $msg = "";
        if ((isset($_POST['enter'])) || ($countSet === 0)) {
            if (isset($_POST['answerQuestion_A'])) {
                $answerA = trim($_POST['answerQuestion_A']);
            }
            if (isset($_POST['answerQuestion_B'])) {
                $answerB = trim($_POST['answerQuestion_B']);
            }
            if (isset($_POST['answerQuestion_C'])) {
                $answerC = trim($_POST['answerQuestion_C']);
            }
            if ($countSet > 0) {
                if (isset($_POST['security_question_A']) != 0) {
                    $questionA = trim($_POST['security_question_A']);
                    $continue = checkSecurityQ($account_id, $questionA, $answerA);
                }
            }
            if ($countSet > 1) {
                if (isset($_POST['security_question_B'])) {
                    $questionB = trim($_POST['security_question_B']);
                    if ($questionB == $questionA) {
                        $answerA = "";
                        $answerB = "";
                        $answerC = "";
                        $questionA = 0;
                        $questionB = 0;
                        $questionC = 0;
                        $msg .= "<span style='color:red'><br/>Question 1 and Question 2 are identical please change one or both of them!<br/></span>";
                        $continue = 0;
                    }
                    if ($continue != 0) {
                        $continue = checkSecurityQ($account_id, $questionB, $answerB);
                    }
                }
            }
            if ($countSet > 2) {
                if (isset($_POST['security_question_C'])) {
                    $questionC = trim($_POST['security_question_C']);
                    if ($questionA == $questionC) {
                        $answerA = "";
                        $answerB = "";
                        $answerC = "";
                        $questionA = 0;
                        $questionB = 0;
                        $questionC = 0;
                        $msg .= "<span style='color:red'><br/>Question 1 and Question 3 are identical please change one or both of them!<br/></span>";
                        $continue = 0;
                    }
                    if ($questionB == $questionC) {
                        $answerA = "";
                        $answerB = "";
                        $answerC = "";
                        $questionA = 0;
                        $questionB = 0;
                        $questionC = 0;
                        $msg .= "<span style='color:red'><br/>Question 2 and Question 3 are identical please change one or both of them!<br/></span>";
                        $continue = 0;
                    }
                    if ($continue != 0) {
                        $continue = checkSecurityQ($account_id, $questionC, $answerC);
                    }

                }
            }
            if ($continue != 0 || $countSet === 0) {
                $report = resetPassword($_SESSION['recovery_email']);
                $_SESSION['title'] = $report->title;
                $_SESSION['msg'] = $report->msg;
                $_SESSION['nextModal'] = $report->nextModal;
                $_SESSION['success'] = $report->success;
                $_SESSION['inputs'] = $report->inputs;
                unset($_SESSION['recovery_email']);
                header("Location: index.php");
                die();

                //send recover to mailer code goes here
                //$msg = "<span style='color:green'>You've Made it!</span>";
            } else {
                $report = new Report("Error", "Too many reset attempts on record. Contact an admin for assistance.", "", false);
                $_SESSION['title'] = $report->title;
                $_SESSION['msg'] = $report->msg;
                $_SESSION['nextModal'] = $report->nextModal;
                $_SESSION['success'] = $report->success;
                $_SESSION['inputs'] = $report->inputs;
                unset($_SESSION['recovery_email']);
                header("Location: index.php");
                die();

                //$msg.="<span style='color:red'><br/>Error, perhaps you made one to many attempts if this problem persists contact support!<br/></span>";
            }
        }

        $modal = '<div id="securityModal" class="w3-modal">
    <div class="w3-modal-content w3-animate-top w3-card-4">
        <header class="w3-container w3-lime w3-center w3-padding-32">
            <span onclick="document.getElementById(\'securityModal\').style.display=\'none\'"
                  class="w3-button w3-lime w3-xlarge w3-display-topright">×</span>
            <h2 class="w3-wide"><i class="w3-margin-right"></i>Security Questions for ' . $_SESSION['recovery_email'] . ' </h2>
        </header>
        <form action="recovery.php" method="post" class="w3-container">
            <div>';

        if ($countSet > 0) {
            $modal .= "<p><label>Question 1: </label></p>";
            $modal .= '<select class="w3-select w3-border" name="security_question_A" id="security_question_A" required><option value="">Select A Security Question</option>';
            $modal .= loadOnSecurity($account_id);
            $modal .= "</select>";
            $modal .= "<p><label>Answer: </label></p>";
            $modal .= '<input class="w3-input w3-border" type="text" maxlength = "150" value="' . $answerA . '" name="answerQuestion_A" id="answer_Q1" required  />';
        }
        if ($countSet > 1) {
            $modal .= "<p><label>Question 2: </label></p>";
            $modal .= '<select class="w3-select w3-border" name="security_question_B" id="security_question_B" required><option value="">Select A Security Question</option>';
            $modal .= loadOnSecurity($account_id);
            $modal .= "</select>";
            $modal .= "<p><label>Answer: </label></p>";
            $modal .= '<input class="w3-input w3-border" type="text" maxlength = "150" value="' . $answerB . '" name="answerQuestion_B" id="answer_Q2" required  />';
        }
        if ($countSet > 2) {
            $modal .= "<p><label>Question 3: </label></p>";
            $modal .= '<select class="w3-select w3-border" name="security_question_C" id="security_question_C" required><option value="">Select A Security Question</option>';
            $modal .= loadOnSecurity($account_id);
            $modal .= "</select>";
            $modal .= "<p><label>Answer: </label></p>";
            $modal .= '<input class="w3-input w3-border" type="text" maxlength = "150" value="' . $answerC . '" name="answerQuestion_C" id="answer_Q3" required  />';
        }

        $modal .= '</div>
            <input type="hidden" id="email" name="email" value="' . $_SESSION['recovery_email'] . '">
            <button class="w3-button w3-block w3-lime w3-padding-16 w3-section w3-right" type="submit" name="enter">Submit</button>
        </form>
    </div>
</div>';
        echo $modal;
    }
}
?>




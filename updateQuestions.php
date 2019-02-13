<?php
require_once "database.php";

require_once "session.php";
function enableNewSecurity($accountID, $question_ID, $answer) {// var for new questions and answers
    $con = Connection::connect();
    $isFirst = 0;
    $question = "";
    $stmt = $con->prepare("SELECT questions FROM `RecoveryQuestionsList` WHERE question_ID = ? AND active = ?");
    $stmt->bindValue(1, $question_ID, PDO::PARAM_STR);
    $stmt->bindValue(2, 1, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $question = $row['questions'];
    if ($row['questions'] == null) {
        $con = null;
        return false;
    }
    $stmt = $con->prepare("SELECT question_Number FROM `RecoveryQuestions` WHERE account_ID = ? AND question = ? ");
    $stmt->bindValue(1, $accountID, PDO::PARAM_INT);
    $stmt->bindValue(2, $question, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row['question_Number'] != null) {
        $stmt = $con->prepare("UPDATE `RecoveryQuestions` SET answer = ? WHERE account_ID = ? AND question_Number = ? ");
        $stmt->bindValue(1, $answer, PDO::PARAM_STR);
        $stmt->bindValue(2, $accountID, PDO::PARAM_INT);
        $stmt->bindValue(3, $row['question_Number'], PDO::PARAM_INT);
        $stmt->execute();
        $con = null;
        return true;
    }
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
function loadSecurityOptions() {
    $con = Connection::connect();
    $set = 0;
    $question = '';
    $query = "SELECT DISTINCT * FROM RecoveryQuestionsList WHERE active = '1' ORDER BY questions ASC";
    $statement = $con->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();
    foreach ($result as $row) {
        $question .= '<option value=' . $row['question_ID'] . '>' . $row['questions'] . '</option>';
        $set = 1;
    }
    $con = null;
    return $question;
}//loadOnSecurity

$answer = "";
if (isset($_POST['enter']) && isset($_POST['answerQuestion'])) {
    $question = Input::int($_POST['set_question']);
    $answer = Input::str($_POST['answerQuestion']);

    $response = enableNewSecurity($_SESSION["profile_ID"], $question, $answer);//change 4 to account id
    if (!$response) {
        $report = new Report("Error", "Security question not set.", "", false);
        $_SESSION['title'] = $report->title;
        $_SESSION['msg'] = $report->msg;
        $_SESSION['nextModal'] = $report->nextModal;
        $_SESSION['success'] = $report->success;
        $_SESSION['inputs'] = $report->inputs;
        //$msg="<span style='color:red'><br/>Error, overlapping question!<br/></span>";
    } else {
        $report = new Report("Success", "Security question added successfully.", "", true);
        $_SESSION['title'] = $report->title;
        $_SESSION['msg'] = $report->msg;
        $_SESSION['nextModal'] = $report->nextModal;
        $_SESSION['success'] = $report->success;
        $_SESSION['inputs'] = $report->inputs;
        //$msg="<span style='color:green'><br/>Success!<br/></span>";
    }
    header("Location: profile.php");
    die();
}
?>

<div id="questionModal" class="w3-modal">
    <div class="w3-modal-content w3-animate-top w3-card-4">
        <header class="w3-container w3-lime w3-center w3-padding-32">
            <span onclick="document.getElementById('questionModal').style.display='none'"
                  class="w3-button w3-lime w3-xlarge w3-display-topright">×</span>
            <h2 class="w3-wide"><i class="w3-margin-right"></i>Set Security Questions </h2>
        </header>
        <form action="updateQuestions.php" method="post" class="w3-container">
            <div>
                <?php
                echo '<p><label>Select A Security Question:</label></p>';
                echo '<select class="w3-select w3-border" name="set_question" id="set_question" required><option value="">Select A Security Question</option>';
                echo loadSecurityOptions();
                echo "</select><br/>";
                echo '<p><label>Answer:</label></p>';
                echo '<input class="w3-input w3-border" type="text" maxlength = "150" value="' . $answer . '" name="answerQuestion" id="answer_Q" required />';
                ?>
            </div>
            <br/>
            <button name="enter" class="w3-button w3-block w3-lime w3-padding-16 w3-section w3-right" type="submit">Add</button>
            <button type="button" class="w3-button w3-red w3-section"
                    onclick="document.getElementById('questionModal').style.display='none'">Close
                <i class="fa fa-remove"></i>
            </button>
        </form>
    </div>
</div>
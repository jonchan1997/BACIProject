<?php
require_once "session.php";
require_once "database.php";

$allowEdit = FALSE;
$trustedUser = FALSE;

if (isset($_REQUEST["action"]) || isset($_REQUEST["delete"]) || isset($_POST['submit'])) {
    if (isset($_SESSION["profile_ID"]) && isset($_SESSION["account_ID"])) {
        if ($_SESSION["profile_ID"] == $_SESSION["account_ID"]) {
            // user is editing own account
            $allowEdit = TRUE;
            $profile_account_id = $_SESSION["profile_ID"];
        } elseif ($type > 2) {
            // user is an admin, not a coordinator, performing an edit action
            $profile_account_id = $_SESSION["profile_ID"];
        } elseif ($_REQUEST['action'] == "sendMentorshipRequest") {
            // user is only attempting to do a mentorship request
            $profile_account_id = $_SESSION["profile_ID"];
        } else {
            $_SESSION['title'] = "Error: Forbidden Access";
            $_SESSION['msg'] = "Contact an admin if you believe this is a error.";
            header("location: index.php");
            die();
        }
    } else {
        header("location: index.php");
        die();
    }
} else {
    if (isset($_SESSION["account_ID"])) {
        if (isset($_REQUEST['user'])) {
            // User parameter set, so we show that user's page.
            $profile_account_id = $_REQUEST['user'];
            $_SESSION["profile_ID"] = $_REQUEST['user'];
            if ($_SESSION["account_ID"] == $_REQUEST['user']) {
                $allowEdit = TRUE;
            } elseif ($type > 2) {
                $allowEdit = TRUE;
            }
        } else {
            // Without a user parameter set, redirect to logged in user's own profile.
            $profile_account_id = $_SESSION["account_ID"];
            $_SESSION["profile_ID"] = $_SESSION["account_ID"];
            $allowEdit = TRUE;
        }
    } elseif (isset($_REQUEST['user'])) {
        // No user is logged in
        $profile_account_id = $_REQUEST['user'];
        $_SESSION["profile_ID"] = $_REQUEST['user'];
    }
}

if(isset($_SESSION["profile_ID"])){
    $con = Connection::connect();

    $stmt = $con->prepare("SELECT * FROM `Account` WHERE active = 0 AND account_ID = ?");
    $stmt->bindValue(1, $_SESSION['profile_ID'], PDO::PARAM_INT);
    $stmt->execute();

    $result = $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if($result != null){
        $con = null;

        $_SESSION['title'] = "Invalid Profile";
        $_SESSION['msg'] = "I'm sorry, but that profile has been deleted.";
        $_SESSION['nextModal'] = "";
        $_SESSION['success'] = FALSE;
        $_SESSION['inputs'] = null;

        header("Location: index.php");
        die();
    }

    $con = null;
}

if (!isset($profile_account_id)) {
    header("Location: index.php");
    die();
}

if (isset($_SESSION["account_ID"])) {
    $currentMentorships = getCurrentMentorships($profile_account_id);
    $approvedUserArr = array();
    foreach($currentMentorships as $mentorship) {
        array_push($approvedUserArr, $mentorship['mentor_ID'], $mentorship['mentee_ID']);
    }
    $approvedUserArr = array_unique($approvedUserArr);
    if (in_array($_SESSION["account_ID"], $approvedUserArr)) {
        $trustedUser = TRUE;
    }
}

if (isset($_POST['delete']) && !isset($_POST['submit']) && isset($_SESSION["profile_ID"]) && isset($_SESSION["account_ID"])) {
    if ($_SESSION["profile_ID"] == $_SESSION["account_ID"]) {
        deleteAccount($profile_account_id);

        $_SESSION['title'] = "Account Deleted";
        $_SESSION['msg'] = "You have been logged out.";
        $_SESSION['nextModal'] = "";
        $_SESSION['success'] = TRUE;
        $_SESSION['inputs'] = null;
        header("Location: logout.php");
        die();

    } elseif ($type > 2) {
        $report = deleteAccount($profile_account_id);

        $_SESSION['title'] = $report->title;
        $_SESSION['msg'] = $report->msg;
        $_SESSION['nextModal'] = $report->nextModal;
        $_SESSION['success'] = $report->success;
        $_SESSION['inputs'] = $report->inputs;
        header("Location: index.php");
        die();
    }
}

if (isset($_POST['submit']) && isset($_FILES['profile'])) {
    $image_dir = 'images';
    $image_dir_path = getcwd() . DIRECTORY_SEPARATOR . $image_dir;

    $file_name = $_FILES['profile']['name'];
    $file_size = $_FILES['profile']['size'];
    $file_tmp = $_FILES['profile']['tmp_name'];
    $file_type = $_FILES['profile']['type'];
    $file_ext = strtolower(end(explode('.',$_FILES['profile']['name'])));

    $target = $image_dir_path . DIRECTORY_SEPARATOR . $file_name;
    move_uploaded_file($file_tmp, $target);

    registerNewPicture($profile_account_id, $target);
    header("location: profile.php?user=" . $profile_account_id);
    die();
} elseif (isset($_POST['submit']) && isset($_FILES['resume'])) {
    $image_dir = 'documents';
    $image_dir_path = getcwd() . DIRECTORY_SEPARATOR . $image_dir;

    $file_name = $_FILES['resume']['name'];
    $file_size = $_FILES['resume']['size'];
    $file_tmp = $_FILES['resume']['tmp_name'];
    $file_type = $_FILES['resume']['type'];
    $file_ext = strtolower(end(explode('.',$_FILES['resume']['name'])));

    $target = $image_dir_path . DIRECTORY_SEPARATOR . $file_name;
    move_uploaded_file($file_tmp, $target);

    registerNewResume($profile_account_id, $target);
    header("location: profile.php?user=" . $profile_account_id);
    die();
} elseif (isset($_POST['submit'])) {
    $con = Connection::connect();

    if (isset($_POST['gender'])) {
        $stmt = $con->prepare("UPDATE Information set gender = ? where account_ID = ?");
        $stmt->bindValue(1, Input::int($_POST['gender']), PDO::PARAM_INT);
        $stmt->bindValue(2, $profile_account_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    if (isset($_POST['status'])) {
        $stmt = $con->prepare("UPDATE Information set status = ? where account_ID = ?");
        $stmt->bindValue(1, Input::int($_POST['status']), PDO::PARAM_INT);
        $stmt->bindValue(2, $profile_account_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    if (isset($_POST['email'])) {
        $stmt = $con->prepare("UPDATE Information set email_address = ? where account_ID = ?");
        $stmt->bindValue(1, Input::email($_POST['email']), PDO::PARAM_STR);
        $stmt->bindValue(2, $profile_account_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    if (isset($_POST['phone'])) {
        $stmt = $con->prepare("select phone_number from `Phone Numbers` where account_ID = ?");
        $stmt->bindValue(1, $profile_account_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($row) == 0) {
            registerNewPhoneNumber($profile_account_id, preg_replace("/[^0-9]/", "", Input::str($_POST['phone'])));
        } else {
            $stmt = $con->prepare("UPDATE `Phone Numbers` set phone_number = ? where account_ID = ?");
            $stmt->bindValue(1, preg_replace("/[^0-9]/", "", Input::str($_POST['phone'])), PDO::PARAM_INT);
            $stmt->bindValue(2, $profile_account_id, PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    if (isset($_POST['addr1']) && isset($_POST['addr2']) && isset($_POST['city']) && isset($_POST['profile_state']) && isset($_POST['postcode']) && isset($_POST['country'])) {
        $address = new Address(Input::str($_POST['addr1']), Input::str($_POST['addr2']), Input::str($_POST['city']), Input::str($_POST['postcode']), Input::int($_POST['profile_state']), Input::int($_POST['country']));

        $old_address_id = getAddressIDFromAccount($profile_account_id);
        if ($old_address_id != null) {
            $stmt = $con->prepare("UPDATE `Address History` set end = CURRENT_TIMESTAMP where address_id = ?");
            $stmt->bindValue(1, $old_address_id, PDO::PARAM_INT);
            $stmt->execute();
        }

        updateUserAddress($profile_account_id, $address);
    }

    if (isset($_POST['profile_facebook'])) {
        $stmt = $con->prepare("UPDATE Information set facebook = ? where account_ID = ?");
        $stmt->bindValue(1, Input::str($_POST['profile_facebook']), PDO::PARAM_STR);
        $stmt->bindValue(2, $profile_account_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    if (isset($_POST['profile_linkedin'])) {
        $stmt = $con->prepare("UPDATE Information set linkedin = ? where account_ID = ?");
        $stmt->bindValue(1, Input::str($_POST['profile_linkedin']), PDO::PARAM_STR);
        $stmt->bindValue(2, $profile_account_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    if (isset($_POST['profile_twitter'])) {
        $stmt = $con->prepare("UPDATE Information set twitter = ? where account_ID = ?");
        $stmt->bindValue(1, Input::str($_POST['profile_twitter']), PDO::PARAM_STR);
        $stmt->bindValue(2, $profile_account_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    if (isset($_POST['preference'])) {
        $stmt = $con->prepare("UPDATE Information set mentorship_preference = ? where account_ID = ?");
        $stmt->bindValue(1, Input::int($_POST['preference']), PDO::PARAM_STR);
        $stmt->bindValue(2, $profile_account_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    if (isset($_POST['job_ID'])) {
        if (isset($_POST['delete'])) {
            $stmt = $con->prepare("DELETE FROM `Job History` where job_ID = ?");
            $stmt->bindValue(1, Input::int($_POST['job_ID']), PDO::PARAM_INT);
            $stmt->execute();
            $con = null;
            echo '<h2 class="w3-text-grey w3-padding-16"><i class="fa fa-suitcase fa-fw w3-margin-right w3-xxlarge w3-text-lime"></i>Work Experience</h2>' . formatJobs(getJobs($profile_account_id)) . makeHistoryElementEditable(true, "jobs");
            die();
        } else {
            if ($_POST['job_ID'] == -1) {
                // adding new degree
                $stmt = $con->prepare("insert into `Job History` (`account_ID`, employer, profession_field, `start`, `end`) values (?, ?, ?, ?, ?)");
                $stmt->bindValue(1, $profile_account_id, PDO::PARAM_INT);
                $stmt->bindValue(2, Input::str($_POST['employer']), PDO::PARAM_STR);
                $stmt->bindValue(3, Input::str($_POST['title']), PDO::PARAM_STR);
                $stmt->bindValue(4, Input::int($_POST['start']), PDO::PARAM_INT);
                $stmt->bindValue(5, Input::int($_POST['end']), PDO::PARAM_INT);

                $stmt->execute();
            } else {
                $stmt = $con->prepare("UPDATE `Job History` set employer = ?, profession_field = ?, start = ?, `end` = ? where job_ID = ?");
                $stmt->bindValue(1, Input::str($_POST['employer']), PDO::PARAM_STR);
                $stmt->bindValue(2, Input::str($_POST['title']), PDO::PARAM_STR);
                $stmt->bindValue(3, Input::int($_POST['start']), PDO::PARAM_INT);
                $stmt->bindValue(4, Input::int($_POST['end']), PDO::PARAM_INT);
                $stmt->bindValue(5, Input::int($_POST['job_ID']), PDO::PARAM_INT);

                $stmt->execute();
            }
        }
    }

    if (isset($_POST['degree_ID'])) {
        if (isset($_POST['delete'])) {
            $stmt = $con->prepare("DELETE FROM `Degrees` where degree_ID = ?");
            $stmt->bindValue(1, Input::int($_POST['degree_ID']), PDO::PARAM_INT);
            $stmt->execute();
            $con = null;
            echo '<h2 class="w3-text-grey w3-padding-16"><i class="fa fa-certificate fa-fw w3-margin-right w3-xxlarge w3-text-lime"></i>Education</h2>' . formatDegrees(getDegrees($profile_account_id)) . makeHistoryElementEditable(true, "degrees");
            die();
        } else {
            if ($_POST['degree_ID'] == -1) {
                // adding new degree
                $stmt = $con->prepare("insert into Degrees (account_ID, degree_type_ID, school, major, graduation_year, enrollment_year) values (?, ?, ?, ?, ?, ?)");
                $stmt->bindValue(1, $profile_account_id, PDO::PARAM_INT);
                $stmt->bindValue(2, Input::int($_POST['degreeType']), PDO::PARAM_INT);
                $stmt->bindValue(3, Input::str($_POST['school']), PDO::PARAM_STR);
                $stmt->bindValue(4, Input::str($_POST['major']), PDO::PARAM_STR);
                $stmt->bindValue(5, Input::int($_POST['end']), PDO::PARAM_INT);
                $stmt->bindValue(6, Input::int($_POST['start']), PDO::PARAM_INT);

                $stmt->execute();
            } else {
                $stmt = $con->prepare("UPDATE `Degrees` set degree_type_ID = ?, school = ?, major = ?, graduation_year = ?, enrollment_year = ? where degree_ID = ?");
                $stmt->bindValue(1, Input::int($_POST['degreeType']), PDO::PARAM_INT);
                $stmt->bindValue(2, Input::str($_POST['school']), PDO::PARAM_STR);
                $stmt->bindValue(3, Input::str($_POST['major']), PDO::PARAM_STR);
                $stmt->bindValue(4, Input::int($_POST['end']), PDO::PARAM_INT);
                $stmt->bindValue(5, Input::int($_POST['start']), PDO::PARAM_INT);
                $stmt->bindValue(6, Input::int($_POST['degree_ID']), PDO::PARAM_INT);

                $stmt->execute();
            }
        }
    }

    if (isset($_POST['privilege'])) {
        if ($_POST['privilege'] < $type) {
            $stmt = $con->prepare("UPDATE Account set type = ? where account_ID = ?");
            $stmt->bindValue(1, Input::int($_POST['privilege']), PDO::PARAM_INT);
            $stmt->bindValue(2, $profile_account_id, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $_SESSION['title'] = "Error Upgrading User";
            $_SESSION['msg'] = "Privilege levels mismatch.";
            $_SESSION['nextModal'] = "";
            $_SESSION['success'] = FALSE;
            $_SESSION['inputs'] = null;
        }
    }

    if (isset($_POST['firstName'])) {
        $stmt = $con->prepare("UPDATE Information set first_name = ? where account_ID = ?");
        $stmt->bindValue(1, Input::str($_POST['firstName']), PDO::PARAM_STR);
        $stmt->bindValue(2, $profile_account_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    if (isset($_POST['middleName'])) {
        $stmt = $con->prepare("UPDATE Information set middle_name = ? where account_ID = ?");
        $stmt->bindValue(1, Input::str($_POST['middleName']), PDO::PARAM_STR);
        $stmt->bindValue(2, $profile_account_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    if (isset($_POST['lastName'])) {
        $stmt = $con->prepare("UPDATE Information set last_name = ? where account_ID = ?");
        $stmt->bindValue(1, Input::str($_POST['lastName']), PDO::PARAM_STR);
        $stmt->bindValue(2, $profile_account_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    $con = null;
    header("location: profile.php?user=" . $profile_account_id);
    die();
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "addEmptyJob") {
    echo '  <form method="post" class="w3-container w3-text-grey" action="profile.php">
            <p><span>Company:</span></p>
            <input class="w3-input w3-border" type="text" value="" name="employer"/>
            <p><span>Job Title/Field:</span></p>
            <input class="w3-input w3-border" type="text" value="" name="title"/>
            <p><span>Start Year:</span></p>
            <input class="w3-input w3-border" type="text" value="" name="start"/>
            <p><span>End Year:</span></p>
            <input class="w3-input w3-border" type="text" value="" name="end"/>
            <input type="hidden" id="degree_ID" name="job_ID" value="-1">
            <button type="submit" name="submit" class="w3-button w3-third w3-lime w3-section">Save</button>
            <button type="button" class="w3-button w3-third w3-red w3-section" onclick="">Delete</button>
            <hr></form>';
    die();
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "addEmptyDegree") {
    echo '<form method="post" class="w3-container w3-text-grey" action="profile.php"><p><span>Degree Type:</span></p><select name="degreeType" id="degreeType" class="w3-select w3-border">' . listDegreeTypes() . '</select><p><span>Major:</span></p><input class="w3-input w3-border" type="text" value="" name="major"/><p><span>University/College:</span></p><input class="w3-input w3-border" type="text" value="" name="school"/><p><span>Enrollment Year:</span></p><input class="w3-input w3-border" type="text" value="" name="start"/><p><span>Graduation Year:</span></p><input class="w3-input w3-border" type="text" value="" name="end"/><input type="hidden" id="degree_ID" name="degree_ID" value="-1"><button type="submit" name="submit" class="w3-button w3-third w3-lime w3-section">Save</button><button type="button" class="w3-button w3-third w3-red w3-section" onclick="">Delete</button><hr></form>';
    die();
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "getFormattedDegrees") {
    echo '<h2 class="w3-text-grey w3-padding-16"><i class="fa fa-certificate fa-fw w3-margin-right w3-xxlarge w3-text-lime"></i>Education</h2>' . formatDegrees(getDegrees($profile_account_id)) . makeHistoryElementEditable(true, "degrees");
    die();
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "getFormattedJobs") {
    echo '<h2 class="w3-text-grey w3-padding-16"><i class="fa fa-suitcase fa-fw w3-margin-right w3-xxlarge w3-text-lime"></i>Work Experience</h2>' . formatJobs(getJobs($profile_account_id)) . makeHistoryElementEditable(true, "jobs");
    die();
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "getEditableFormattedDegrees") {
    echo formatDegreesEditable(getDegrees($profile_account_id), $profile_account_id);
    die();
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "getEditableFormattedJobs") {
    echo formatJobsEditable(getJobs($profile_account_id), $profile_account_id);
    die();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == "handlePendingRequest") {
    $user = $_REQUEST['user'];
    $pending = $_REQUEST['pending'];
    $response = $_REQUEST['response'];

    $report = pendingMentorshipResponse($user, $pending, $response);

    $_SESSION['title'] = $report->title;
    $_SESSION['msg'] = $report->msg;
    $_SESSION['nextModal'] = $report->nextModal;
    $_SESSION['success'] = $report->success;
    $_SESSION['inputs'] = $report->inputs;

    echo formatPendingMentorships($profile_account_id);
    die();
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "endMentorship") {
    $mentorship_ID = $_REQUEST['id'];

    $report = endMentorship($_SESSION['account_ID'], $mentorship_ID);

    $_SESSION['title'] = $report->title;
    $_SESSION['msg'] = $report->msg;
    $_SESSION['nextModal'] = $report->nextModal;
    $_SESSION['success'] = $report->success;
    $_SESSION['inputs'] = $report->inputs;

    echo formatMentorships($profile_account_id);
    die();
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == "sendMentorshipRequest"){
    $user = $profile_account_id;
    $proposerID = $_SESSION['account_ID'];

    $report = proposeMentorship($user, $proposerID, $proposerID);

    $_SESSION['title'] = $report->title;
    $_SESSION['msg'] = $report->msg;
    $_SESSION['nextModal'] = $report->nextModal;
    $_SESSION['success'] = $report->success;
    $_SESSION['inputs'] = $report->inputs;

    //header("Location: profile.php?user=" . $profile_account_id);
    die();
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == "formatAdminPairingBox"){
    print formatAdminPairingBox();
    die();
}

function makeEditable($allowEdit, $id) {
    if ($allowEdit) {
        return ' <a class="w3-button w3-display-right" onclick="enterEditState(\'' . $id . '\');"><i class="fa fa-pencil fa-fw w3-large w3-text-lime w3-opacity"></i></a>';
    } else {
        return "";
    }
}

function makeHistoryElementEditable($allowEdit, $id) {
    if ($allowEdit) {
        return ' <a class="w3-button w3-display-topright w3-margin" onclick="enterHistoryElementEditState(\'' . $id . '\');"><i class="fa fa-pencil fa-fw w3-large w3-text-lime w3-opacity"></i></a>';
    } else {
        return "";
    }
}

function putItInASpan($thing) {
    return "<span>" . $thing . "</span>";
}

function formatDegrees($degrees) {
    $result = "";
    foreach($degrees as $degree) {
        $result .= '<hr><div class="w3-container w3-margin-bottom"><h5 class="w3-opacity">';
        $result .= $degree[5] . " in " . $degree[1] . " / " . $degree[0];
        $result .= '</h5><h6 class="w3-text-lime"><i class="fa fa-calendar fa-fw w3-margin-right"></i>';
        if($degree[2] == 0000){
            $result .= $degree[3] . " - present";
        }
        else{
            $result .= $degree[3] . " - " . $degree[2];
        }

        $result .= '</h6></div>';
    }
    return $result;
}

function formatDegreesEditable($degrees, $profile_account_ID) {
    $result = '<h2 class="w3-text-grey w3-padding-16"><i class="fa fa-certificate fa-fw w3-margin-right w3-xxlarge w3-text-lime"></i>Education</h2>';
    $result .= '<button name="addDegree" class="w3-button w3-third w3-lime w3-section" onclick="addEmptyDegree();">Add Degree</button>';
    $result .= '<button name="cancel" class="w3-button w3-third w3-red w3-section" onclick="exitHistoryElementEditState(\'degrees\');">Cancel</button>';
    foreach($degrees as $degree) {
        $result .= '<form method="post" class="w3-container w3-text-grey" action="profile.php">';
        $result .= '<p><span>Degree Type:</span></p>';
        $result .= '<select name="degreeType" id="degreeType" class="w3-select w3-border">' . listDegreeTypes() . "</select>";
        $result .= '<p><span>Major:</span></p>';
        $result .= '<input class="w3-input w3-border" type="text" value="' . $degree[1] . '" name="major"/>';
        $result .= '<p><span>University/College:</span></p>';
        $result .= '<input class="w3-input w3-border" type="text" value="' . $degree[0] . '" name="school"/>';
        $result .= '<p><span>Enrollment Year:</span></p>';
        $result .= '<input class="w3-input w3-border" type="text" value="' . $degree[3] . '" name="start"/>';
        $result .= '<p><span>Graduation Year:</span></p>';
        $result .= '<input class="w3-input w3-border" type="text" value="' . $degree[2] . '" name="end"/>';
        $result .= '<input type="hidden" id="degree_ID" name="degree_ID" value="' . $degree[4] . '">';
        $result .= '<input type="hidden" id="user" name="user" value="' . $profile_account_ID . '">';
        $result .= '<button type="submit" name="submit" class="w3-button w3-third w3-lime w3-section">Edit</button>';
        $result .= '<button type="button" class="w3-button w3-third w3-red w3-section" onclick="deleteHistoryItem(\'degree\', ' . $degree[4] . ')">Delete</button>';
        $result .= '<hr></form>';
    }
    return $result;
}

function formatJobs($jobs) {
    $result = "";
    foreach ($jobs as $job) {
        $result .= '<hr><div class="w3-container w3-margin-bottom"><h5 class="w3-opacity">';
        $result .= $job[1] . " / " . $job[0];
        $result .= '</h5><h6 class="w3-text-lime"><i class="fa fa-calendar fa-fw w3-margin-right"></i>';
        if ($job[3] == 0000) {
            $result .= $job[2] . " - present";
        } else {
            $result .= $job[2] . " - " . $job[3];
        }
        $result .= '</h6></div>';
    }
    return $result;
}

function formatJobsEditable($jobs, $profile_account_ID) {
    $result = '<h2 class="w3-text-grey w3-padding-16"><i class="fa fa-suitcase fa-fw w3-margin-right w3-xxlarge w3-text-lime"></i>Work Experience</h2>';
    $result .= '<button name="addJob" class="w3-button w3-third w3-lime w3-section" onclick="addEmptyJob();">Add Job</button>';
    $result .= '<button name="cancel" class="w3-button w3-third w3-red w3-section" onclick="exitHistoryElementEditState(\'jobs\');">Cancel</button>';
    foreach($jobs as $job) {
        $result .= '<form method="post" class="w3-container w3-text-grey" action="profile.php">';
        $result .= '<p><span>Company:</span></p>';
        $result .= '<input class="w3-input w3-border" type="text" value="' . $job[0] . '" name="employer"/>';
        $result .= '<p><span>Job Title/Field:</span></p>';
        $result .= '<input class="w3-input w3-border" type="text" value="' . $job[1] . '" name="title"/>';
        $result .= '<p><span>Start Year:</span></p>';
        $result .= '<input class="w3-input w3-border" type="text" value="' . $job[2] . '" name="start"/>';
        $result .= '<p><span>End Year:</span></p>';
        $result .= '<input class="w3-input w3-border" type="text" value="' . $job[3] . '" name="end"/>';
        $result .= '<input type="hidden" id="job_ID" name="job_ID" value="' . $job[4] . '">';
        $result .= '<input type="hidden" id="user" name="user" value="' . $profile_account_ID . '">';
        $result .= '<button type="submit" name="submit" class="w3-button w3-third w3-lime w3-section">Edit</button>';
        $result .= '<button type="button" class="w3-button w3-third w3-red w3-section" onclick="deleteHistoryItem(\'job\', ' . $job[4] . ')">Delete</button>';
        $result .= '<hr></form>';
    }
    return $result;
}

function formatMentorships($profile_account_id) {
    $current = getCurrentMentorships($profile_account_id);
    $ended = getEndedMentorships($profile_account_id);
    $combined = array_merge($current, $ended);

    $result = '<table id="mentorship_history_table"><thead><tr><th>Mentor</th><th>Mentee</th><th>Date Began</th><th>Date Ended</th><th>End Mentorship</th></tr></thead><tbody>';

    foreach($combined as $cur) {
        if ($profile_account_id == $cur['mentor_ID']) {
            $mentorLink = getName($cur['mentor_ID']);
            $menteeLink = '<a href="profile.php?user=' . $cur['mentee_ID'] . '">' . getName($cur['mentee_ID']) . '</a>';
        } elseif ($profile_account_id == $cur['mentee_ID']) {
            $mentorLink = '<a href="profile.php?user=' . $cur['mentor_ID'] . '">' . getName($cur['mentor_ID']) . '</a>';
            $menteeLink = getName($cur['mentee_ID']);
        }

        if ($cur['end'] == null) {
            $end = "Ongoing";
            $endMentorship = '<button name="end" class="w3-button w3-red" onclick="endMentorship(\'' . $cur['mentorship_ID'] . '\', \'' . $_SESSION['account_ID'] . '\')">End</button>';
        } else {
            $end = $cur['end'];
            $endMentorship = "";
        }



        $result .= "<tr>";
        $result .= "<th><h6>" . $mentorLink . "</h6></th>";
        $result .= "<th><h6>" . $menteeLink . "</h6></th>";
        $result .= "<th>" . $cur['start'] . "</th>";
        $result .= "<th>" . $end . "</th>";
        $result .= "<th>" . $endMentorship . "</th>";
        $result .= "</tr>";
    }

    $result .= '</tbody></table>';
    return $result;
}

function formatPendingMentorships($profile_account_id) {
    $pending = getPendingMentorships($profile_account_id);


    $result = '<table id="pending_mentorship_history_table"><thead><tr><th>Mentor</th><th>Mentee</th><th>Approve Request</th><th>Delete Request</th></tr></thead><tbody>';

    foreach($pending as $cur) {

        $id = $cur['pending_ID'];
        $disabled = "";
		if($profile_account_id != $_SESSION['account_ID']){
			$disabled = 'disabled=""';
		}
        if($profile_account_id == $cur['mentor_ID'] && $cur['mentor_status'] == 1) {
            $disabled = 'disabled=""';
        } elseif($profile_account_id == $cur['mentee_ID'] && $cur['mentee_status'] == 1) {
            $disabled = 'disabled=""';
        }elseif(isset($_SESSION['type']) && $_SESSION['type'] > 1){
			$disabled = '';
		}

        $accept = '<button ' . $disabled . ' name="accept" class="w3-button w3-lime" onclick="handlePendingMentorship(\'' . $id . '\', 1);">Accept</button>';
        $decline = '<button name="decline" class="w3-button w3-red" onclick="handlePendingMentorship(\'' . $id . '\', 0);">Decline</button>';

        if ($profile_account_id == $cur['mentor_ID']) {
            $mentorLink = getName($cur['mentor_ID']);
            $menteeLink = '<a href="profile.php?user=' . $cur['mentee_ID'] . '">' . getName($cur['mentee_ID']) . '</a>';
        } elseif ($profile_account_id == $cur['mentee_ID']) {
            $mentorLink = '<a href="profile.php?user=' . $cur['mentor_ID'] . '">' . getName($cur['mentor_ID']) . '</a>';
            $menteeLink = getName($cur['mentee_ID']);
        }
        $result .= "<tr>";
        $result .= "<th><h6>" . $mentorLink . "</h6></th>";
        $result .= "<th><h6>" . $menteeLink . "</h6></th>";
        $result .= "<th><h6>" . $accept . "</h6></th>";
        $result .= "<th><h6>" . $decline . "</h6></th>";
        $result .= "</tr>";
    }

    $result .= '</tbody></table>';
    return $result;
}

?>
<!-- template from: https://www.w3schools.com/w3css/w3css_templates.asp -->
<!DOCTYPE html>
<html>
<head>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BAConnect Profile</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="js/closeModals.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
    <script>
        function init() {

        }

        $(document).ready( function () {
            $('#mentorship_history_table').DataTable({
                "paging":   false,
                "ordering": false,
                "info":     false,
                "searching":   false
            });
            $('#pending_mentorship_history_table').DataTable({
                "paging":   false,
                "ordering": false,
                "info":     false,
                "searching":   false
            });
        });

        function showProfileStates(countryID){
            if(countryID != ""){
                let xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function(){
                    if(this.readyState == 4 && this.status == 200){
                        document.getElementById("profile_state").innerHTML = this.responseText;
                    }
                };
                xmlhttp.open("GET", "AJAX.php?action=refreshState&country=" + countryID, true);
                xmlhttp.send();
            }
        }

        function adminStartPair() {
            let xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function(){
                if(this.readyState == 4 && this.status == 200){
                    document.getElementById("adminActionBox").innerHTML = this.responseText;
                }
            };

            xmlhttp.open("POST", "AJAX.php", true);
            xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xmlhttp.send("action=adminStartPair");
        }

        function adminFinishPair() {
            let xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function(){
                if(this.readyState == 4 && this.status == 200){
                    window.location = "index.php?" + this.responseText;
                }
            };

            xmlhttp.open("POST", "AJAX.php", true);
            xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xmlhttp.send("action=adminFinishPair");
        }

        function adminClearPair() {
            let xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function(){
                if(this.readyState == 4 && this.status == 200){
                    document.getElementById("adminActionBox").innerHTML = this.responseText;
                }
            };

            xmlhttp.open("POST", "AJAX.php", true);
            xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xmlhttp.send("action=adminClearPair");
        }

        function endMentorship(mentorship_ID, account_ID) {
            let xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function(){
                if(this.readyState == 4 && this.status == 200){
                    $('#mentorship_history_table').DataTable().destroy();
                    document.getElementById("mentorships_content").innerHTML = this.responseText;
                    $('#mentorship_history_table').DataTable({
                        "paging":   false,
                        "ordering": false,
                        "info":     false,
                        "searching":   false
                    });
                    location.reload();
                }
            };

            xmlhttp.open("POST", "profile.php", true);
            xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xmlhttp.send("action=endMentorship&id=" + mentorship_ID + "&account=" + account_ID);
        }

        function handlePendingMentorship(pending_id, accept = 0) {
            let xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function(){
                if(this.readyState == 4 && this.status == 200){
                    $('#pending_mentorship_history_table').DataTable().destroy();
                    document.getElementById("pending_content").innerHTML = this.responseText;
                    $('#pending_mentorship_history_table').DataTable({
                        "paging":   false,
                        "ordering": false,
                        "info":     false,
                        "searching":   false
                    });
                    location.reload();
                }
            };

            xmlhttp.open("POST", "profile.php", true);
            xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xmlhttp.send("action=handlePendingRequest&user=<?php echo $profile_account_id?>&pending=" + pending_id+ "&response=" +  accept);
        }

        function sendMentorshipRequest() {
            let xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function(){
                if(this.readyState == 4 && this.status == 200){
                    $('#request').attr("disabled",true);
                    location.reload();
                }
            };

            xmlhttp.open("POST", "profile.php", true);
            xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xmlhttp.send("action=sendMentorshipRequest");
        }

        function enterHistoryElementEditState(id) {
            let xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function(){
                if(this.readyState == 4 && this.status == 200){
                    document.getElementById(id).innerHTML = this.responseText;
                }
            };

            if (id == "jobs") {
                xmlhttp.open("GET", "profile.php?action=getEditableFormattedJobs", true);
            } else if (id == "degrees") {
                xmlhttp.open("GET", "profile.php?action=getEditableFormattedDegrees", true);
            }

            xmlhttp.send();
        }

        function exitHistoryElementEditState(id) {
            let xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function(){
                if(this.readyState == 4 && this.status == 200){
                    document.getElementById(id).innerHTML = this.responseText;
                }
            };

            if (id == "jobs") {
                xmlhttp.open("GET", "profile.php?action=getFormattedJobs", true);
            } else if (id == "degrees") {
                xmlhttp.open("GET", "profile.php?action=getFormattedDegrees", true);
            }

            xmlhttp.send();
        }

        function addEmptyJob() {
            let xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function(){
                if(this.readyState == 4 && this.status == 200){
                    document.getElementById("jobs").innerHTML += this.responseText;
                }
            };
            xmlhttp.open("GET", "profile.php?action=addEmptyJob", true);
            xmlhttp.send();
        }

        function addEmptyDegree() {
            let xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function(){
                if(this.readyState == 4 && this.status == 200){
                    document.getElementById("degrees").innerHTML += this.responseText;
                }
            };
            xmlhttp.open("GET", "profile.php?action=addEmptyDegree", true);
            xmlhttp.send();
        }

        function deleteHistoryItem(type, id) {
            if (type == "job") {
                let xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function(){
                    if(this.readyState == 4 && this.status == 200){
                        //location.reload();
                        document.getElementById("jobs").innerHTML = this.responseText;
                    }
                };

                xmlhttp.open("POST", "profile.php", true);
                xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xmlhttp.send("submit=&delete=&job_ID=" + id);
            } else if (type == "degree") {
                let xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function(){
                    if(this.readyState == 4 && this.status == 200){
                        //location.reload();
                        document.getElementById("degrees").innerHTML = this.responseText;
                    }
                };

                xmlhttp.open("POST", "profile.php", true);
                xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xmlhttp.send("submit=&delete=&degree_ID=" + id);
            }
        }

        function exitEditState(id) {
            document.getElementById(id).classList.remove("w3-cell-row");
            if (id == "gender") {
                document.getElementById(id).innerHTML = `<i class="fa fa-user fa-fw w3-margin-right w3-large w3-text-lime"></i><?php echo putItInASpan(getGender($profile_account_id)) . makeEditable($allowEdit, "gender")?>`;
            } else if (id == "status") {
                document.getElementById(id).innerHTML = `<i class="fa fa-briefcase fa-fw w3-margin-right w3-large w3-text-lime"></i><?php echo putItInASpan(getStatus($profile_account_id)) . makeEditable($allowEdit, "status")?>`;
            } else if (id == "profile_email") {
                document.getElementById(id).innerHTML = `<i class="fa fa-envelope fa-fw w3-margin-right w3-large w3-text-lime"></i><?php echo putItInASpan(getEmail($profile_account_id)) . makeEditable($allowEdit, "profile_email")?>`;
            } else if (id == "phone") {
                document.getElementById(id).innerHTML = `<i class="fa fa-phone fa-fw w3-margin-right w3-large w3-text-lime"></i><?php echo putItInASpan(getFormattedPhoneNumber($profile_account_id)) . makeEditable($allowEdit, "phone")?>`;
            } else if (id == "location") {
                document.getElementById(id).innerHTML = `<i class="fa fa-home fa-fw w3-margin-right w3-large w3-text-lime"></i><?php echo putItInASpan(getApproximateLocation($profile_account_id)) . makeEditable($allowEdit, "location")?>`;
                document.getElementById("countrySpan").innerHTML = `<i class="fa fa-globe fa-fw w3-margin-right w3-large w3-text-lime"></i><?php echo putItInASpan(getCountry($profile_account_id))?>`;
            } else if (id == "profile_facebook") {
                document.getElementById(id).innerHTML = `<i class="fa fa-facebook-square fa-fw w3-margin-right w3-large w3-text-lime"></i><?php echo putItInASpan(getFacebookLink($profile_account_id)) . makeEditable($allowEdit, "profile_facebook")?>`;
            } else if (id == "profile_linkedin") {
                document.getElementById(id).innerHTML = `<i class="fa fa-linkedin-square fa-fw w3-margin-right w3-large w3-text-lime"></i><?php echo putItInASpan(getLinkedinLink($profile_account_id)) . makeEditable($allowEdit, "profile_linkedin")?>`;
            } else if (id == "profile_twitter") {
                document.getElementById(id).innerHTML = `<i class="fa fa-twitter-square fa-fw w3-margin-right w3-large w3-text-lime"></i><?php echo putItInASpan(getTwitterLink($profile_account_id)) . makeEditable($allowEdit, "profile_twitter")?>`;
            } else if (id == "preference") {
                document.getElementById(id).innerHTML = `<i class="fa fa-users fa-fw w3-margin-right w3-large w3-text-lime"></i><?php echo putItInASpan(getUserMentorshipPreference($profile_account_id)) . makeEditable($allowEdit, "preference")?>`;
            } else if (id == "profile_privilege") {
                document.getElementById(id).innerHTML = `<i class="fa fa-lock fa-fw w3-margin-right w3-large w3-text-lime"></i><?php echo putItInASpan(getPrivilege($profile_account_id)) . makeEditable($allowEdit, "profile_privilege")?>`;
            }
        }

        function enterEditState(id) {
            document.getElementById(id).className += " w3-cell-row";
            if (id == "gender") {
                document.getElementById(id).innerHTML = `
                    <p><i class="fa fa-user fa-fw w3-margin-right w3-large w3-text-lime"></i>Gender:</p>
                    <form method="post" action="profile.php">
                    <select class="w3-select w3-border w3-cell" name="gender" id="gender">
                        <option value="0"> Male </option>
                        <option value="1"> Female </option>
                        <option value="2"> Nonbinary/Other </option>
                    </select>
                    <input type="hidden" id="user" name="user" value="<?php echo $profile_account_id; ?>">
                    <button class="w3-button w3-half w3-lime w3-cell w3-margin-top" type="submit" name="submit">Edit Gender</button>
                    <button class="w3-button w3-half w3-red w3-cell w3-margin-top" type="button" onclick="exitEditState('gender');">Cancel</button>
                    </form>`;
            } else if (id == "status") {
                document.getElementById(id).innerHTML = `
                    <p><i class="fa fa-briefcase fa-fw w3-margin-right w3-large w3-text-lime"></i>Status:</p>
                    <form method="post" action="profile.php">
                    <select class="w3-select w3-border w3-cell" name="status" id="status">
                        <option value="0"> Student </option>
                        <option value="1"> Working Professional </option>
                    </select>
                    <input type="hidden" id="user" name="user" value="<?php echo $profile_account_id; ?>">
                    <button class="w3-button w3-half w3-lime w3-cell w3-margin-top" type="submit" name="submit">Edit Status</button>
                    <button class="w3-button w3-half w3-red w3-cell w3-margin-top" type="button" onclick="exitEditState('status');">Cancel</button>
                    </form>`;
            } else if (id == "profile_email") {
                document.getElementById(id).innerHTML = `
                    <p><i class="fa fa-envelope fa-fw w3-margin-right w3-large w3-text-lime"></i>Email:</p>
                    <form method="post" action="profile.php">
                    <input class="w3-input w3-border w3-cell" type="text" maxlength="50" value="<?php echo getEmail($profile_account_id); ?>" name="email" id="email"/>
                    <input type="hidden" id="user" name="user" value="<?php echo $profile_account_id; ?>">
                    <button class="w3-button w3-half w3-lime w3-cell w3-margin-top" type="submit" name="submit">Edit Email</button>
                    <button class="w3-button w3-half w3-red w3-cell w3-margin-top" type="button" onclick="exitEditState('profile_email');">Cancel</button>
                    </form>`;
            } else if (id == "phone") {
                document.getElementById(id).innerHTML = `
                    <p><i class="fa fa-phone fa-fw w3-margin-right w3-large w3-text-lime"></i>Phone Number:</p>
                    <form method="post" action="profile.php">
                    <input class="w3-input w3-border w3-cell" type="tel" value="<?php echo getFormattedPhoneNumber($profile_account_id); ?>" name="phone"/>
                    <input type="hidden" id="user" name="user" value="<?php echo $profile_account_id; ?>">
                    <button class="w3-button w3-half w3-lime w3-cell w3-margin-top" type="submit" name="submit">Edit Phone</button>
                    <button class="w3-button w3-half w3-red w3-cell w3-margin-top" type="button" onclick="exitEditState('phone');">Cancel</button>
                    </form>`;
            } else if (id == "location") {
                document.getElementById(id).innerHTML = `
                    <form method="post" action="profile.php">

                    <p><i class="fa fa-globe fa-fw w3-margin-right w3-large w3-text-lime"></i>Country:</p>
                    <select class="w3-select w3-border w3-cell" name="country" id="country" onchange="showProfileStates(this.value);">
                        <?php echo listCountries($profile_account_id) ?>
                    </select>

                    <p><i class="fa fa-home fa-fw w3-margin-right w3-large w3-text-lime"></i>Address Line 1:</p>
                    <input class="w3-input w3-border" type="text" value="<?php echo getAddressLine1($profile_account_id); ?>" name="addr1"/>

                    <p><i class="fa fa-home fa-fw w3-margin-right w3-large w3-text-lime"></i>Address Line 2:</p>
                    <input class="w3-input w3-border" type="text" value="<?php echo getAddressLine2($profile_account_id); ?>" name="addr2"/>

                    <p><i class="fa fa-home fa-fw w3-margin-right w3-large w3-text-lime"></i>City:</p>
                    <input class="w3-input w3-border" type="text" value="<?php echo getCity($profile_account_id); ?>" name="city"/>

                    <p><i class="fa fa-home fa-fw w3-margin-right w3-large w3-text-lime"></i>State:</p>
                    <select class="w3-select w3-border" name="profile_state" id="profile_state">
                        <?php echo getStatesList(getCountryID($profile_account_id), $profile_account_id); ?>
                    </select>

                    <p><i class="fa fa-home fa-fw w3-margin-right w3-large w3-text-lime"></i>Post code:</p>
                    <input class="w3-input w3-border" type="text" value="<?php echo getPostCode($profile_account_id); ?>" name="postcode"/>

                    <input type="hidden" id="user" name="user" value="<?php echo $profile_account_id; ?>">

                    <button class="w3-button w3-half w3-lime w3-cell w3-margin-top" type="submit" name="submit">Edit Location</button>
                    <button class="w3-button w3-half w3-red w3-cell w3-margin-top" type="button" onclick="exitEditState('location');">Cancel</button>
                    </form>`;
                document.getElementById("countrySpan").innerHTML = " ";
            } else if (id == "profile_facebook") {
                document.getElementById(id).innerHTML = `
                    <p><i class="fa fa-facebook-square fa-fw w3-margin-right w3-large w3-text-lime"></i>Facebook:</p>
                    <form method="post" action="profile.php">
                    <input class="w3-input w3-border w3-cell" type="text" maxlength="50" value="<?php echo getFacebookLink($profile_account_id); ?>" name="profile_facebook" id="profile_facebook"/>
                    <input type="hidden" id="user" name="user" value="<?php echo $profile_account_id; ?>">
                    <button class="w3-button w3-half w3-lime w3-cell w3-margin-top" type="submit" name="submit">Edit Facebook</button>
                    <button class="w3-button w3-half w3-red w3-cell w3-margin-top" type="button" onclick="exitEditState('profile_facebook');">Cancel</button>
                    </form>`;
            } else if (id == "profile_linkedin") {
                document.getElementById(id).innerHTML = `
                    <p><i class="fa fa-linkedin-square fa-fw w3-margin-right w3-large w3-text-lime"></i>Linkedin:</p>
                    <form method="post" action="profile.php">
                    <input class="w3-input w3-border w3-cell" type="text" maxlength="50" value="<?php echo getLinkedinLink($profile_account_id); ?>" name="profile_linkedin" id="profile_linkedin"/>
                    <input type="hidden" id="user" name="user" value="<?php echo $profile_account_id; ?>">
                    <button class="w3-button w3-half w3-lime w3-cell w3-margin-top" type="submit" name="submit">Edit Linkedin</button>
                    <button class="w3-button w3-half w3-red w3-cell w3-margin-top" type="button" onclick="exitEditState('profile_linkedin');">Cancel</button>
                    </form>`;
            } else if (id == "profile_twitter") {
                document.getElementById(id).innerHTML = `
                    <p><i class="fa fa-twitter-square fa-fw w3-margin-right w3-large w3-text-lime"></i>Twitter:</p>
                    <form method="post" action="profile.php">
                    <input class="w3-input w3-border w3-cell" type="text" maxlength="50" value="<?php echo getTwitterLink($profile_account_id); ?>" name="profile_twitter" id="profile_twitter"/>
                    <input type="hidden" id="user" name="user" value="<?php echo $profile_account_id; ?>">
                    <button class="w3-button w3-half w3-lime w3-cell w3-margin-top" type="submit" name="submit">Edit Twitter</button>
                    <button class="w3-button w3-half w3-red w3-cell w3-margin-top" type="button" onclick="exitEditState('profile_twitter');">Cancel</button>
                    </form>`;
            } else if (id == "preference") {
                document.getElementById(id).innerHTML = `
                    <p><i class="fa fa-users fa-fw w3-margin-right w3-large w3-text-lime"></i>Mentorship Preference:</p>
                    <form method="post" action="profile.php">
                    <select class="w3-select w3-border w3-cell" name="preference" id="preference">
                        <option <?php if(getUserMentorshipPreference($profile_account_id) == "Mentor"){echo("selected");}?> value="0"> Mentor </option>
                        <option <?php if(getUserMentorshipPreference($profile_account_id) == "Mentee"){echo("selected");}?> value="1"> Mentee </option>
                        <option <?php if(getUserMentorshipPreference($profile_account_id) == "Not Interested"){echo("selected");}?> value="2"> Not Interested </option>
                    </select>
                    <input type="hidden" id="user" name="user" value="<?php echo $profile_account_id; ?>">
                    <button class="w3-button w3-half w3-lime w3-cell w3-margin-top" type="submit" name="submit">Edit Preference</button>
                    <button class="w3-button w3-half w3-red w3-cell w3-margin-top" type="button" onclick="exitEditState('preference');">Cancel</button>
                    </form>`;
            } else if (id == "profile_privilege") {
                document.getElementById(id).innerHTML = `
                    <p><i class="fa fa-lock fa-fw w3-margin-right w3-large w3-text-lime"></i>Privilege Level:</p>
                    <form method="post" action="profile.php">
                    <select class="w3-select w3-border w3-cell" name="privilege" id="privilege">
                        <?php if(isset($_SESSION["account_ID"])) { echo getUpgradeTiers($_SESSION["account_ID"]); } ?>
                    </select>
                    <input type="hidden" id="user" name="user" value="<?php echo $profile_account_id; ?>">
                    <button class="w3-button w3-half w3-lime w3-cell w3-margin-top" type="submit" name="submit">Edit Privilege</button>
                    <button class="w3-button w3-half w3-red w3-cell w3-margin-top" type="button" onclick="exitEditState('profile_privilege');">Cancel</button>
                    </form>`;
            }
        }
    </script>
</head>

<body class="w3-light-grey" onload="init();">
<!-- Navbar -->
<?php include "header.php"; ?>
<!-- Page content -->
<div class="w3-content" style="max-width:1400px;">

    <!-- The Grid -->
    <div class="w3-row-padding">

        <!-- Left Column -->
        <div class="w3-third">

            <div class="w3-white w3-text-grey w3-card-4">
                <div class="w3-display-container">
                    <img src="<?php echo file_get_contents("http://corsair.cs.iupui.edu:22891/courseproject/image.php?account_id=" . $profile_account_id); ?>" style="width:100%;" alt="Avatar">
                    <div class="w3-display-middle w3-display-hover w3-xlarge">
                        <?php if ($allowEdit) { echo "<button class=\"w3-button w3-black\" onclick=\"document.getElementById('uploadPicModal').style.display='block'\">Change Picture...</button>";} ?>
                    </div>
                    <div class="w3-display-bottomleft w3-container w3-text-black" style="width: 100%;">
                        <h2 id="name" class="w3-text-white w3-display-container" style="text-shadow:1px 1px 0 #444; width: 100%;"><span><?php
                            echo getName($profile_account_id);
                            if ($allowEdit) {
                                echo '<a class="w3-button w3-display-right" onclick="document.getElementById(\'changeNameModal\').style.display=\'block\'"><i class="fa fa-pencil fa-fw w3-large w3-text-lime w3-opacity"></i></a>';
                            } ?></span></h2>
                    </div>
                </div>
                <div class="w3-container">
                    <p class="w3-display-container" id="preference"><i class="fa fa-users fa-fw w3-margin-right w3-large w3-text-lime"></i><?php echo putItInASpan(getUserMentorshipPreference($profile_account_id)) . makeEditable($allowEdit, "preference")?></p>
                    <p class="w3-display-container" id="preference"><i class="fa fa-users fa-fw w3-margin-right w3-large w3-text-lime"></i><?php echo putItInASpan(getMentorshipStatus($profile_account_id)); ?></p>
                    <hr>
                    <p class="w3-display-container" id="gender"><i class="fa fa-user fa-fw w3-margin-right w3-large w3-text-lime"></i><?php echo putItInASpan(getGender($profile_account_id)) . makeEditable($allowEdit, "gender")?></p>
                    <p class="w3-display-container" id="status"><i class="fa fa-briefcase fa-fw w3-margin-right w3-large w3-text-lime"></i><?php echo putItInASpan(getStatus($profile_account_id)) . makeEditable($allowEdit, "status")?></p>
                    <p class="w3-display-container" id="location"><i class="fa fa-home fa-fw w3-margin-right w3-large w3-text-lime"></i><?php echo putItInASpan(getApproximateLocation($profile_account_id)) . makeEditable($allowEdit, "location")?></p>
                    <p class="w3-display-container" id="countrySpan"><i class="fa fa-globe fa-fw w3-margin-right w3-large w3-text-lime"></i><?php echo putItInASpan(getCountry($profile_account_id))?></p>
                    <?php if ($allowEdit || $trustedUser) {
                        echo "<hr>";
                        echo '<p class="w3-display-container" id="profile_email"><i class="fa fa-envelope fa-fw w3-margin-right w3-large w3-text-lime"></i>' . putItInASpan(getEmail($profile_account_id)) . makeEditable($allowEdit, "profile_email") . '</p>';
                        echo '<p class="w3-display-container" id="phone"><i class="fa fa-phone fa-fw w3-margin-right w3-large w3-text-lime"></i>' . putItInASpan(getFormattedPhoneNumber($profile_account_id)) . makeEditable($allowEdit, "phone") . '</p>';
                    } ?>
                    <hr>
                    <p class="w3-display-container" id="profile_facebook"><i class="fa fa-facebook-square fa-fw w3-margin-right w3-large w3-text-lime"></i><?php echo putItInASpan(getFacebookLink($profile_account_id)) . makeEditable($allowEdit, "profile_facebook")?></p>
                    <p class="w3-display-container" id="profile_linkedin"><i class="fa fa-linkedin-square fa-fw w3-margin-right w3-large w3-text-lime"></i><?php echo putItInASpan(getLinkedinLink($profile_account_id)) . makeEditable($allowEdit, "profile_linkedin")?></p>
                    <p class="w3-display-container" id="profile_twitter"><i class="fa fa-twitter-square fa-fw w3-margin-right w3-large w3-text-lime"></i><?php echo putItInASpan(getTwitterLink($profile_account_id)) . makeEditable($allowEdit, "profile_twitter")?></p>

                    <?php if (isset($_SESSION["account_ID"]) && getAccountTypeFromAccountID($_SESSION["account_ID"]) > 1) {
                        echo '<hr>';
                        echo '<p class="w3-display-container" id="profile_privilege"><i class="fa fa-lock fa-fw w3-margin-right w3-large w3-text-lime"></i>' . putItInASpan(getPrivilege($profile_account_id)) . makeEditable($allowEdit, "profile_privilege") . '</p>';
                    } ?>

                    <?php if (isset($_SESSION["account_ID"]) && $profile_account_id != $_SESSION["account_ID"]) {
                        $disabled = "";
                        if (hasAlreadySentRequest($profile_account_id)) { $disabled = "disabled=''"; }
                        echo "<hr><button id='request' " . $disabled . " class='w3-button w3-block w3-dark-grey w3-margin-bottom' onclick='sendMentorshipRequest()'>+ Connect</button>";
                    } ?>

                    <?php if ($allowEdit) {
                        // show resume
                        echo '<hr><p class="w3-display-container" id="profile_resume"><button class="w3-button w3-half w3-lime w3-cell" type="button" name="upload" onclick="document.getElementById(\'uploadResumeModal\').style.display=\'block\'">Upload Resume</button><a class="w3-button w3-half w3-lime w3-cell" type="button" name="download" href="resume.php">Download Resume</a><br>';
                    } ?>

                    <?php if (isset($_SESSION["account_ID"]) && $type > 1) {
                        echo "<div id='adminActionBox'>";
                        echo formatAdminPairingBox();
                        echo "</div>";
                    } ?>

                    <?php if ($allowEdit) {
                        echo '<hr><p class="w3-display-container" id="profile_security"><button style="width: 100%" class="w3-button w3-lime w3-cell" type="button" name="security" onclick="document.getElementById(\'questionModal\').style.display=\'block\'">Add Security Question</button><br>';
                        echo '<p class="w3-display-container" id="profile_del"><button style="width: 100%" class="w3-button w3-red w3-cell" type="button" name="delete" onclick="document.getElementById(\'deleteAccountModal\').style.display=\'block\'">Delete Account</button><br>';
                    } ?>

                </div>
            </div><br>

            <!-- End Left Column -->
        </div>

        <!-- Right Column -->
        <div class="w3-twothird">

            <?php if ($type > 1 || $allowEdit) { echo "
            <div id=\"pending\" class=\"w3-container w3-display-container w3-card w3-white w3-margin-bottom\">
                <h2 class=\"w3-text-grey w3-padding-16\"><i class=\"fa fa-users fa-fw w3-margin-right w3-xxlarge w3-text-lime\"></i>Pending Mentorships</h2>
                <div id=\"pending_content\" class=\"w3-container w3-text-grey\" style=\"padding-bottom:32px\">
                    " . formatPendingMentorships($profile_account_id) . "
                </div>
            </div>"; } ?>

            <div id="degrees" class="w3-container w3-display-container w3-card w3-white w3-margin-bottom">
                <h2 class="w3-text-grey w3-padding-16"><i class="fa fa-certificate fa-fw w3-margin-right w3-xxlarge w3-text-lime"></i>Education</h2>
                <?php echo formatDegrees(getDegrees($profile_account_id)) . makeHistoryElementEditable($allowEdit, "degrees"); ?>
            </div>

            <div id="jobs" class="w3-container w3-display-container w3-card w3-white w3-margin-bottom">
                <h2 class="w3-text-grey w3-padding-16"><i class="fa fa-suitcase fa-fw w3-margin-right w3-xxlarge w3-text-lime"></i>Work Experience</h2>
                <?php echo formatJobs(getJobs($profile_account_id)) . makeHistoryElementEditable($allowEdit, "jobs"); ?>
            </div>

            <?php if ($type > 1 || $allowEdit) { echo '
            <div id="mentorships" class="w3-container w3-display-container w3-card w3-white w3-margin-bottom">
                <h2 class="w3-text-grey w3-padding-16"><i class="fa fa-users fa-fw w3-margin-right w3-xxlarge w3-text-lime"></i>Mentorships</h2>
                <div id="mentorships_content" class="w3-container w3-text-grey" style="padding-bottom:32px">
                    ' . formatMentorships($profile_account_id) . '
                </div>
            </div>'; } ?>
            <!-- End Right Column -->
        </div>
        <!-- End Grid -->
    </div>

<?php

$name = getNameArray($profile_account_id);

if ($allowEdit) { echo "
<div id=\"uploadPicModal\" class=\"w3-modal\">
        <div class=\"w3-modal-content w3-animate-top w3-card-4\">
            <header class=\"w3-container w3-lime w3-center w3-padding-32\">
            <span onclick=\"document.getElementById('uploadPicModal').style.display='none'\"
                  class=\"w3-button w3-lime w3-xlarge w3-display-topright\">×</span>
                <h2 class=\"w3-wide\"><i class=\"w3-margin-right\"></i>Change Profile Picture </h2>
            </header>
            <form method=\"post\" action=\"profile.php\" enctype='multipart/form-data' class=\"w3-container\">
                <p>
                    <label>
                        <i class=\"fa fa-user\"></i> New picture:
                    </label>
                </p>
                <input class=\"w3-input w3-border\" type=\"file\" placeholder=\"\" name=\"profile\" id=\"profile\" accept=\"image/png, image/jpeg\">
                <button class=\"w3-button w3-block w3-lime w3-padding-16 w3-section w3-right\" type=\"submit\" name=\"submit\">
                    Submit New Photo
                    <i class=\"fa fa-check\"></i>
                </button>
                <button type=\"button\" class=\"w3-button w3-red w3-section\"
                        onclick=\"document.getElementById('uploadPicModal').style.display='none'\">Close
                    <i class=\"fa fa-remove\"></i>
                </button>
            </form>
        </div>
    </div>

    <div id=\"uploadResumeModal\" class=\"w3-modal\">
        <div class=\"w3-modal-content w3-animate-top w3-card-4\">
            <header class=\"w3-container w3-lime w3-center w3-padding-32\">
            <span onclick=\"document.getElementById('uploadResumeModal').style.display='none'\"
                  class=\"w3-button w3-lime w3-xlarge w3-display-topright\">×</span>
                <h2 class=\"w3-wide\"><i class=\"w3-margin-right\"></i>Change Profile Resume </h2>
            </header>
            <form method=\"post\" action=\"profile.php\" enctype='multipart/form-data' class=\"w3-container\">
                <p>
                    <label>
                        <i class=\"fa fa-user\"></i> New resume:
                    </label>
                </p>
                <input class=\"w3-input w3-border\" type=\"file\" placeholder=\"\" name=\"resume\" id=\"resume\">
                <button class=\"w3-button w3-block w3-lime w3-padding-16 w3-section w3-right\" type=\"submit\" name=\"submit\">
                    Submit New Resume
                    <i class=\"fa fa-check\"></i>
                </button>
                <button type=\"button\" class=\"w3-button w3-red w3-section\"
                        onclick=\"document.getElementById('uploadResumeModal').style.display='none'\">Close
                    <i class=\"fa fa-remove\"></i>
                </button>
            </form>
        </div>
    </div>

    <div id=\"deleteAccountModal\" class=\"w3-modal\">
        <div class=\"w3-modal-content w3-animate-top w3-card-4\">
            <header class=\"w3-container w3-lime w3-center w3-padding-32\">
            <span onclick=\"document.getElementById('deleteAccountModal').style.display='none'\"
                  class=\"w3-button w3-lime w3-xlarge w3-display-topright\">×</span>
                <h2 class=\"w3-wide\"><i class=\"w3-margin-right\"></i>Delete Account</h2>
            </header>
            <form method=\"post\" action=\"profile.php\" enctype='multipart/form-data' class=\"w3-container\">
                <h2>Are you sure you want to delete this account?</h2>
                <button class=\"w3-button w3-block w3-lime w3-padding-16 w3-section w3-right\" type=\"submit\" name=\"delete\">
                    Confirm Account Deletion
                    <i class=\"fa fa-check\"></i>
                </button>
                <button type=\"button\" class=\"w3-button w3-red w3-section\"
                        onclick=\"document.getElementById('deleteAccountModal').style.display='none'\">Close
                    <i class=\"fa fa-remove\"></i>
                </button>
            </form>
        </div>
    </div>

    <div id=\"changeNameModal\" class=\"w3-modal\">
        <div class=\"w3-modal-content w3-animate-top w3-card-4\">
            <header class=\"w3-container w3-lime w3-center w3-padding-32\">
            <span onclick=\"document.getElementById('changeNameModal').style.display='none'\"
                  class=\"w3-button w3-lime w3-xlarge w3-display-topright\">×</span>
                <h2 class=\"w3-wide\"><i class=\"w3-margin-right\"></i>Change Name</h2>
            </header>
            <form method=\"post\" action=\"profile.php\" enctype='multipart/form-data' class=\"w3-container\">
                <p><label><i class=\"fa fa-user\"></i>First Name: </label></p>
                <input class=\"w3-input w3-border\" type=\"text\" maxlength=\"50\" value=\"" . $name[0] . "\" name=\"firstName\" id=\"firstName\" required/>
                <p><label><i class=\"fa fa-user\"></i>Middle Name: </label></p>
                <input class=\"w3-input w3-border\" type=\"text\" maxlength=\"50\" value=\"" . $name[1] . "\" name=\"middleName\" id=\"middleName\"/>
                <p><label><i class=\"fa fa-user\"></i>Last Name: </label></p>
                <input class=\"w3-input w3-border\" type=\"text\" maxlength=\"50\" value=\"" . $name[2] . "\" name=\"lastName\" id=\"lastName\"/>
                <input type=\"hidden\" id=\"action\" name=\"action\" value=\"\">
                <button class=\"w3-button w3-block w3-lime w3-padding-16 w3-section w3-right\" type=\"submit\" name=\"submit\">Change Name <i class=\"fa fa-check\"></i></button>
                <button type=\"button\" class=\"w3-button w3-red w3-section\"
                        onclick=\"document.getElementById('changeNameModal').style.display='none'\">Close
                    <i class=\"fa fa-remove\"></i>
                </button>
            </form>
        </div>
    </div>

    ";
include "updateQuestions.php"; }
?>

    <!-- End Page Container -->
</div>
<!-- End Page Content -->
</body>
</html>

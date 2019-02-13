<?php
include_once "database.php";

if (isset($_GET["id"])) {
    echo createCard($_GET["id"]);
}

function formatDegreesAndJobs($degrees, $jobs) {
    $result = "";

    foreach($degrees as $degree) {
        if($degree[2] == 0000){
            $result = $result . "<p style='margin:0.5em;'><span class='w3-text-lime'><i class='fa fa-graduation-cap'></i><b> " . $degree[3] . "-present</b></span> " . $degree[0] ."</p>";
        }
        else{
            $result = $result . "<p style='margin:0.5em;'><span class='w3-text-lime'><i class='fa fa-graduation-cap'></i><b> " . $degree[3] . "-" .$degree[2] . "</b></span> " . $degree[0] ."</p>";
        }
        $result = $result . "<p style='margin:0.5em;'><span class=''><b>" . $degree[5] . "</b></span> in " . $degree[1] . "</p>";
    }

    if (count($jobs) == 0) {
        return $result;
    }

    $result = $result . "<hr>";
    foreach($jobs as $job) {
        if ($job[3] == 0000) {
            $result = $result . "<p style='margin:0.5em;'><span class='w3-text-lime'><i class='fa fa-briefcase'></i><b> " . $job[2] . "-present" . "</b></span> " . $job[1] ."</p>";
        } else {
            $result = $result . "<p style='margin:0.5em;'><span class='w3-text-lime'><i class='fa fa-briefcase'></i><b> " . $job[2] . "-" .$job[3] . "</b></span> " . $job[1] ."</p>";
        }
        $result = $result . "<p style='margin:0.5em;'>" . $job[0]. "</p>";
    }
    return $result;
}

function createCard($account_id) {
    // get database entries for user
    // final product looks like this: https://www.w3schools.com/w3css/tryit.asp?filename=tryw3css_cards_buttons2

    $con = Connection::connect();
    $stmt = $con->prepare("select * from Account where account_ID = ? and active = 0");
    $stmt->bindValue(1, $account_id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row != null) {
        return "";
    }

    $image = "data:image/jpeg;base64, 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";

    $user = User::fromID($account_id);
    $location = $user->formatCityAndState();
    if ($location == "") {
        $location = getApproximateLocation($account_id);
    }
    //$imageSrc = file_get_contents("http://corsair.cs.iupui.edu:22891/courseproject/image.php?account_id=" . $account_id);
    //$imageSrc = "http://corsair.cs.iupui.edu:22891/courseproject/image.php?account_id=" . $account_id;

    return '<div class="w3-container" style="display: inline-block; text-align: center; order: ' . $account_id . ';">
  <div class="w3-card-4 w3-margin-bottom">
  <header class="w3-container w3-pale-red">
    <h3>'.$user->formatName().'</h3>
  </header>
  <div class="w3-container w3-text-grey w3-white">
  <div class="w3-row-padding">
    <div class="w3-third">
        <div class = "w3-padding-16" style="position: relative; top: 50%;">
                <img id="' . $account_id . '" class="w3-circle w3-border" src="' . $image . '" style="width: 100%;" alt="Avatar">
        </div>
        <p style="margin:0.25em;">' . getUserMentorshipPreference($account_id) . " / " . $user->formatGender() . '</p>
    </div>
    <div class="w3-twothird w3-small" style="text-align: left;">' . formatDegreesAndJobs(getDegrees($account_id), getJobs($account_id)) . '</div>
  </div>
  <hr>
  <p>' . $location . '</p>
  </div>
  <a class="w3-button w3-block w3-dark-grey" href="profile.php?user=' . $account_id . '">+ View Profile</a>
  </div></div>';
}

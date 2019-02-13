<?php
require_once "session.php";
require_once "database.php";

if ($type < 2) {
    header("location: index.php");
    die();
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "reproposeMentorship") {
    if (isset($_REQUEST['id'])) {
        $con = Connection::connect();
        $stmt = $con->prepare("SELECT * FROM `Mentorship` WHERE mentorship_ID = ?");
        $stmt->bindValue(1, $_REQUEST['id'], PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "mentor=" . getUsernameFromAccountID($result['mentor_ID']) . "&mentee=" . getUsernameFromAccountID($result['mentee_ID']) . "&match=";
        die();
    }
}

function formatMentorships() {
    $mentorships = getEndedMentorships();
    $result = "<table id='ended_mentorships' class='display'><thead><tr><th>Mentor</th><th>Mentee</th><th>Start Date</th><th>End Date</th><th>Ended By</th><th>Repropose Mentorship</th></tr></thead><tbody>";
    foreach($mentorships as $cur) {

        $id = $cur['mentorship_ID'];

        $repropose = '<button name="repropose" class="w3-button w3-lime" onclick="reproposeMentorship(\'' . $id . '\');">Repropose</button>';
        $mentorLink = '<a href="profile.php?user=' . $cur['mentor_ID'] . '">' . getName($cur['mentor_ID']) . '</a>';
        $menteeLink = '<a href="profile.php?user=' . $cur['mentee_ID'] . '">' . getName($cur['mentee_ID']) . '</a>';
        $enderLink = '<a href="profile.php?user=' . $cur['terminator_ID'] . '">' . getName($cur['terminator_ID']) . '</a>';

        $result .= "<tr>";
        $result .= "<th><h6>" . $mentorLink . "</h6></th>";
        $result .= "<th><h6>" . $menteeLink . "</h6></th>";
        $result .= "<th><h6>" . $cur['start'] . "</h6></th>";
        $result .= "<th><h6>" . $cur['end'] . "</h6></th>";
        $result .= "<th><h6>" . $enderLink . "</h6></th>";
        $result .= "<th><h6>" . $repropose . "</h6></th>";
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
    <title>BAConnect Admin</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.dataTables.min.css">
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.html5.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.flash.min.js"></script>

    <script src="js/registration.js"></script>
    <script src="js/closeModals.js"></script>
    <script>
        function reproposeMentorship(mentorship_ID, account_ID) {
            let xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function(){
                if(this.readyState == 4 && this.status == 200){
                    window.location = "index.php?" + this.responseText;
                }
            };
            xmlhttp.open("POST", "endedmentorships.php", true);
            xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xmlhttp.send("action=reproposeMentorship&id=" + mentorship_ID);
        }

        $(document).ready(function () {
            var d = new Date($.now());
            $('#ended_mentorships').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'csv',
                        text: 'Download as CSV',
                        filename: 'EndedMentorships_'+d.getDate()+"-"+(d.getMonth()+1)+"-"+d.getFullYear()+" "+d.getHours()+":"+d.getMinutes()+":"+d.getSeconds(),
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5]
                        }
                    }
                ]
            });
        });

    </script>
</head>

<body class="w3-light-grey" onload="init();">
<!-- Navbar -->
<?php include "header.php"; ?>
<!-- Page content -->
<div class="w3-content w3-display-container" style="max-width:1400px;">
    <div class="w3-container w3-left"><h1 class="">Ended Mentorships</h1></div>
</div>
<div class="w3-content" style="max-width:1400px;">
    <div id="table_container" class="w3-container w3-card w3-white w3-padding-large">
        <?php echo formatMentorships() ?>
    </div>
</div>
</body>
</html>

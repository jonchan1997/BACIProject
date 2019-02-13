<?php
require_once "session.php";
require_once "database.php";

if ($type < 2) {
    header("location: index.php");
    die();
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "handlePendingRequest") {
    $pendingMentorship_ID = $_REQUEST['pending_ID'];
	$response = $_REQUEST['response'];
    $report = pendingMentorshipResponse($_SESSION['account_ID'], $pendingMentorship_ID, $response);
    if ($report->success) {
        echo formatPendingMentorships();
    }
    $_SESSION['title'] = $report->title;
    $_SESSION['msg'] = $report->msg;
    $_SESSION['nextModal'] = $report->nextModal;
    $_SESSION['success'] = $report->success;
    $_SESSION['inputs'] = $report->inputs;

    die();
}

function formatPendingMentorships() {
    $pendingMentorships = getPendingMentorships();
    $result = '<table id="pending_mentorship_history_table" class="display"><thead><tr><th>Mentor</th><th>Mentee</th><th>Request Date</th><th>Approve Request</th><th>Remove Request</th></tr></thead><tbody>';
    foreach($pendingMentorships as $pen) {

        $id = $pen['pending_ID'];

        $approve = '<button name="approve" class="w3-button w3-lime" onclick="handlePendingMentorship(\'' . $id . '\', 1);">Approve</button>';
		$decline = '<button name="decline" class="w3-button w3-red" onclick="handlePendingMentorship(\'' . $id . '\', 0);">Decline</button>';
        $mentorLink = '<a href="profile.php?user=' . $pen['mentor_ID'] . '">' . getName($pen['mentor_ID']) . '</a>';
        $menteeLink = '<a href="profile.php?user=' . $pen['mentee_ID'] . '">' . getName($pen['mentee_ID']) . '</a>';

        $result .= "<tr>";
        $result .= "<th><h6>" . $mentorLink . "</h6></th>";
        $result .= "<th><h6>" . $menteeLink . "</h6></th>";
        $result .= "<th><h6>" . $pen['request_date'] . "</h6></th>";
        $result .= "<th><h6>" . $approve . "</h6></th>";
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
            function handlePendingMentorship(mentorship_ID, accept) {
                let xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function(){
                    if(this.readyState == 4 && this.status == 200){
                        document.getElementById("table_container").innerHTML = this.responseText;
                        $('#pending_mentorship_history_table').DataTable();
                        location.reload();
                    }
                };
                xmlhttp.open("POST", "pendingmentorships.php", true);
                xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xmlhttp.send("action=handlePendingRequest&pending_ID=" + mentorship_ID + "&response=" +  accept);
            }

            $(document).ready(function () {
                var d = new Date($.now());
                $('#pending_mentorship_history_table').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        {
                            extend: 'csv',
                            text: 'Download as CSV',
                            filename: 'PendingMentorships_'+d.getDate()+"-"+(d.getMonth()+1)+"-"+d.getFullYear()+" "+d.getHours()+":"+d.getMinutes()+":"+d.getSeconds(),
                            exportOptions: {
                                columns: [0, 1, 2]
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
        <div class="w3-container w3-left"><h1 class="">Pending Mentorships</h1></div>
    </div>
    <div class="w3-content" style="max-width:1400px;">
        <div id="table_container" class="w3-container w3-card w3-white w3-padding-large">
            <?php echo formatPendingMentorships() ?>
        </div>
    </div>
    </body>
    </html>
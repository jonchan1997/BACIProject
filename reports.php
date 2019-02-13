<?php

if (isset($_POST['userInformation'])) {
    if ($type < 2) {
        $report = new Report("Report Generation Error", "Insufficient privileges.", "", FALSE);
        $_SESSION['title'] = $report->title;
        $_SESSION['msg'] = $report->msg;
        $_SESSION['nextModal'] = $report->nextModal;
        $_SESSION['success'] = $report->success;
        $_SESSION['inputs'] = $report->inputs;
        header("Location: index.php");
        die();
    }

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sample.csv"');
    $data = array(
        'aaa,bbb,ccc,dddd',
        '123,456,789',
        '"aaa","bbb"'
    );

    $fp = fopen('php://output', 'wb');
    foreach ( $data as $line ) {
        $val = explode(",", $line);
        fputcsv($fp, $val);
    }
    fclose($fp);
}

?>

<div id="reportsModal" class="w3-modal">
    <div class="w3-modal-content w3-animate-top w3-card-4">
        <header class="w3-container w3-lime w3-center w3-padding-32">
            <span onclick="document.getElementById('reportsModal').style.display='none'"
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
            <button class="w3-button w3-block w3-lime w3-padding-16 w3-section w3-right" type="submit" name="submit">
                Reset Password
                <i class="fa fa-check"></i>
            </button>
            <button type="button" class="w3-button w3-red w3-section"
                    onclick="document.getElementById('reportsModal').style.display='none'">Close
                <i class="fa fa-remove"></i>
            </button>
        </form>
    </div>
</div>

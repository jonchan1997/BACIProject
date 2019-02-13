<?php
require_once "session.php";
require_once "database.php";

if (isset($_SESSION['title']) && isset($_SESSION['msg']) && isset($_SESSION['nextModal'])) {
    echo makeDialog($_SESSION['title'], $_SESSION['msg'], $_SESSION['nextModal']);
}


if(isset($_SESSION['report'])){
    $report = unserialize($_SESSION['report']);
    echo makeDialog($report->title, $report->msg, $report->nextModal);
}
function makeDialog($title, $message, $nextModal) {
    $result = '<div id="dialogModal" class="w3-modal" style="z-index: 100;"><div class="w3-modal-content w3-animate-top w3-card-4"><header class="w3-container w3-lime w3-center w3-padding-32">';

    if($nextModal == null || $nextModal == ""){
        $result .= '<span onclick="document.getElementById(\'dialogModal\').style.display=\'none\';" class="w3-button w3-lime w3-xlarge w3-display-topright">×</span>';
    }
    else{
        $result .= '<span onclick="document.getElementById(\'dialogModal\').style.display=\'none\'; document.getElementById(\'' . $nextModal . '\').style.display=\'block\';" class="w3-button w3-lime w3-xlarge w3-display-topright">×</span>';
    }

    $result .= '<h2 class="w3-wide"><i class="w3-margin-right"></i>' . $title . '</h2></header>';
    $result .= '<div class="w3-container"><p>' . $message . '</p>';

    if($nextModal == null || $nextModal == ""){
        $result .= '<button type="button" onclick="document.getElementById(\'dialogModal\').style.display=\'none\';" class="w3-button w3-block w3-lime w3-padding-16 w3-section w3-right" name="closeDialog" id="closeDialog">Close</button>';
    }
    else{
        $result .= '<button type="button" onclick="document.getElementById(\'dialogModal\').style.display=\'none\'; document.getElementById(\'' . $nextModal . '\').style.display=\'block\';" class="w3-button w3-block w3-lime w3-padding-16 w3-section w3-right" name="closeDialog" id="closeDialog">Close</button>';
    }
    
    $result .= '</div></div></div>';

    return $result;
}

?>

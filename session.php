<?php

session_start();
//echo print_r($_SESSION);

if (isset($_SESSION['type'])) {
    $type = $_SESSION['type'];
} else {
    $type=0;
}
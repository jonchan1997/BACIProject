<?php
session_start();
//session_unset();
//session_destroy();

unset($_SESSION['account_ID']);
unset($_SESSION['type']);

header("location: index.php");
exit();
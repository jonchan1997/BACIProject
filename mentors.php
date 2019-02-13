<?php
require_once "session.php";
require_once "database.php";
require_once "card.php";
?>
<!-- template from: https://www.w3schools.com/w3css/w3css_templates.asp -->
<!DOCTYPE html>
<html>
<head>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BAConnect Mentors</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="js/registration.js"></script>
    <script src="js/closeModals.js"></script>
    <script src="js/cardHandler.js"></script>
</head>

<body class="w3-light-grey" onload="init();">
<!-- Navbar -->
<?php include "header.php"; ?>
<!-- Page content -->
<div id="cardDisplay" class="flex-container" style="display: flex; flex-wrap: wrap; justify-content: center; align-items: stretch; align-content: flex-start;">

</div>
</body>
<script src="js/search.js"></script>
<script>
    $(window).on("load", function(){
        continuallyLoadCards(30, 0);
        $(window).on("scroll", function(){
            if (($(window).scrollTop() - ($(document).height() - $(window).height()) <= 5) && ($(window).scrollTop() - ($(document).height() - $(window).height()) >= -5)) {
                let term = document.getElementById("searchBox").value;
                if (term == "") {
                    continuallyLoadCards(10, 0);
                } else {
                    searchCards(10, false, 0);
                }
            }
        });
    });
</script>
</html>
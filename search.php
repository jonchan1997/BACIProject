<?php
require_once "session.php";
require_once "database.php";
require_once "card.php";

function buildQueryString($search) {
    $match = "";
    $words = preg_split('/\s+/', $search);
    $columns = array('country', 'state_name', 'city', 'post_code', 'first_name', 'middle_name', 'last_name', 'gender_desc', 'facebook', 'linkedin', 'schools', 'majors', 'degrees', 'employers', 'profession_fields');
    for ($i = 0; $i < count($words); $i++) {
        if ($i != 0) {
            $match .= "AND ";
        }
        for ($j = 0; $j < count($columns); $j++) {
            $match .= "(`" . $columns[$j] . "` LIKE '%" . $words[$i] . "%') ";
            if ($j != count($columns) - 1) {
                $match .= "OR ";
            }
        }
    }
    return $match;
}

if (isset($_POST["action"]) && $_POST["action"] == "loadCards") {
    if (!isset($_POST["offset"])) {
        $offset = 0;
    } else {
        $offset = $_POST["offset"];
    }

    if (!isset($_POST["pref"])) {
        if (!isset($_POST["search"])) {
            $num = $_POST["num"];

            $con = Connection::connect();
            $stmt = $con->prepare("SELECT `account_ID` FROM UserView WHERE active = 1 LIMIT ? OFFSET ?");
            $stmt->bindValue(1, (int)$num, PDO::PARAM_INT);
            $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $con = null;
            echo json_encode($result);
            die();
        } else {
            $num = $_POST["num"];
            $search = Input::str($_POST["search"]);

            $con = Connection::connect();
            $stmt = $con->prepare("SELECT DISTINCT `account_ID` FROM UserAddressGenderJobsDegreesView WHERE " . buildQueryString($search) . " AND active = 1 LIMIT ? OFFSET ?");
            $stmt->bindValue(1, (int)$num, PDO::PARAM_INT);
            $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $con = null;

            echo json_encode($result);
            die();
        }
    } else {
        $pref = Input::int($_POST["pref"]);

        if (!isset($_POST["search"])) {
            $num = $_POST["num"];

            $con = Connection::connect();
            $stmt = $con->prepare("SELECT `account_ID` FROM UserView WHERE (mentorship_preference = ?) AND active = 1 LIMIT ? OFFSET ?");
            $stmt->bindValue(1, (int)$pref, PDO::PARAM_INT);
            $stmt->bindValue(2, (int)$num, PDO::PARAM_INT);
            $stmt->bindValue(3, (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $con = null;
            echo json_encode($result);
            die();
        } else {
            $num = $_POST["num"];
            $search = Input::str($_POST["search"]);

            $con = Connection::connect();
            $stmt = $con->prepare("SELECT DISTINCT `account_ID` FROM UserAddressGenderJobsDegreesView WHERE (mentorship_preference = ?) AND active = 1 " . buildQueryString($search) . " LIMIT ? OFFSET ?");
            $stmt->bindValue(1, (int)$pref, PDO::PARAM_INT);
            $stmt->bindValue(2, (int)$num, PDO::PARAM_INT);
            $stmt->bindValue(3, (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $con = null;

            echo json_encode($result);
            die();
        }
    }
}

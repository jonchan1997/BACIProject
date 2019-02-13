<?php
ini_set("allow_url_fopen", 1);

include_once "dbhelper.php";

function better_array_rand($array, $num_results) {
    if ($num_results > 1) {
        $result = array();
        for ($i = 0; $i < $num_results; $i++) {
            array_push($result, $array[mt_rand(0, count($array) - 1)]);
        }
        return $result;
    } else {
        return $array[mt_rand(0, count($array) - 1)];
    }
}

function generateFakes($number) {
    $collegeCsv = array_map('str_getcsv', file('extras/colleges-list.csv'));
    array_walk($collegeCsv, function(&$a) use ($collegeCsv) {
        $a = array_combine($collegeCsv[0], $a);
    });
    array_shift($collegeCsv);

    $majorCsv = array_map('str_getcsv', file('extras/majors-list.csv'));
    array_walk($majorCsv, function(&$a) use ($majorCsv) {
        $a = array_combine($majorCsv[0], $a);
    });
    array_shift($majorCsv);

    $jobCsv = array_map('str_getcsv', file('extras/jobs-list.csv'));
    array_walk($jobCsv, function(&$a) use ($jobCsv) {
        $a = array_combine($jobCsv[0], $a);
    });
    array_shift($jobCsv);

    $employerCsv = array_map('str_getcsv', file('extras/employers-list.csv'));
    array_walk($employerCsv, function(&$a) use ($employerCsv) {
        $a = array_combine($employerCsv[0], $a);
    });
    array_shift($employerCsv);

    $states = getStatesArray();

    $num = count($states);




    for ($i = 0; $i < $number; $i++) {
        $json = file_get_contents("https://randomuser.me/api/");
        $data = json_decode($json);
        $results = $data->results;
        $results = $results[0];

        $gender = 0;
        if ($results->gender != "male") {
            $gender = 1;
        }

        $state = $states[rand(0, $num - 1)];

        $state_id = $state['state_ID'];
        $country_id = $state['country_ID'];

        $user = new User($results->login->username, $results->login->password, ucfirst($results->name->first), "", ucfirst($results->name->last), $results->email, $gender, preg_replace("/[^0-9]/", "", $results->phone), rand(0,1), rand(0, 2));
        $address = new Address(ucwords($results->location->street), "", ucfirst($results->location->city), $results->location->postcode, $state_id, $country_id);
        $numDegrees = rand(1, 3);
        for ($degreeNum = 0; $degreeNum < $numDegrees; $degreeNum++) {
            $college = $collegeCsv[array_rand($collegeCsv)];
            $major = $majorCsv[array_rand($majorCsv)];

            $degree[$degreeNum] = new EducationHistoryEntry(ucfirst($college[0]), rand(0, 3), ucwords(strtolower($major[1])), rand(1980, 2018), rand(1980, 2018));
        }

        $numJobs = rand(1, 3);
        for ($jobNum = 0; $jobNum < $numJobs; $jobNum++) {
            $employer = $employerCsv[array_rand($employerCsv)];
            $job = $jobCsv[array_rand($jobCsv)];
            $work[$jobNum] = new WorkHistoryEntry(ucfirst($employer[0]), ucfirst($job[0]), rand(1980, 2018), rand(1980, 2018));
        }

        $picture = $results->picture->large;

        $day = rand(1, 28);
        $month = rand(1, 12);
        $year = rand(1950, 2018);

        if($day < 10){
            $day = "0" . $day;
        }
        if($month < 10){
            $month = "0" . $month;
        }

        $dob = $year . "-" . $month . "-" . $day;

        registerUser($user, $address, $degree, $work, $picture, "", $dob);
    }
}

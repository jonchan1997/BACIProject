<?php
require_once "database.php";

class Account {
    public $username;
    public $password;

    function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }
}

class User extends Account {
    public $firstName;
    public $middleName;
    public $lastName;
    public $email;
    public $gender;
    public $phoneNumber;
    public $status;
    public $preference;
    public $dob;

    public $address;

    function __construct($username, $password, $firstName, $middleName, $lastName, $email, $gender, $phoneNumber, $status, $preference, $dob, $address = null) {
        parent::__construct($username, $password);

        $this->firstName = $firstName;
        $this->middleName = $middleName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->gender = $gender;
        $this->phoneNumber = $phoneNumber;
        $this->status = $status;
        $this->preference = $preference;
        $this->dob = $dob;

        $this->address = $address;
    }

    public static function fromID($account_id) {
        $con = Connection::connect();
        $stmt = $con->prepare("SELECT * FROM UserAddressView where account_ID = ?");
        $stmt->bindValue(1, $account_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $con = null;
        if ($row != null) {
            $addr = new Address($row['street_address'], $row['street_address2'], $row['city'], $row['post_code'], $row['state_name'], $row['country']);
            return new self($row['username'], $row['password'], $row['first_name'], $row['middle_name'], $row['last_name'], $row['email_address'], $row['gender'], $row['phone_number'], $row['status'], $row['dob'], $addr);
        } else {
            $con = Connection::connect();
            $stmt = $con->prepare("SELECT * FROM Account where account_ID = ?");
            $stmt->bindValue(1, $account_id, PDO::PARAM_INT);
            $stmt->execute();
            $acc = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $con->prepare("SELECT * FROM Information where account_ID = ?");
            $stmt->bindValue(1, $account_id, PDO::PARAM_INT);
            $stmt->execute();
            $info = $stmt->fetch(PDO::FETCH_ASSOC);

            return new self($acc['username'], $acc['password'], $info['first_name'], $info['middle_name'], $info['last_name'], $info['email_address'], $info['gender'], $info['phone_number'], $info['status'], $info['dob']);
        }
    }

    public function formatName() {
        if ($this->middleName == "") {
            return $this->firstName . " " . $this->lastName;
        } else {
            return $this->firstName . " " . $this->middleName . " " . $this->lastName;
        }
    }

    public function formatStatus() {
        if ($this->status == 0) {
            return "Student";
        } elseif ($this->status == 1) {
            return "Working Professional";
        } else {
            return "Unknown Status";
        }
    }

    public function formatGender() {
        if ($this->gender == 0) {
            return "Male";
        } elseif ($this->gender == 1) {
            return "Female";
        } else {
            return "Nonbinary/Other";
        }
    }

    public function formatCityAndState() {
        if (!is_null($this->address)) {
            return $this->address->city . ", " . $this->address->state;
        } else {
            return "";
        }
    }

}

class Address {
    public $street;
    public $street2;
    public $city;
    public $postcode;
    public $state;
    public $country;

    function __construct($street, $street2, $city, $postcode, $state, $country) {
        $this->street = $street;
        $this->street2 = $street2;
        $this->city = $city;
        $this->postcode = $postcode;
        $this->state = $state;
        $this->country = $country;
    }
}

class EducationHistoryEntry {
    public $schoolName;
    public $degreeType;
    public $degreeMajor;
    public $enrollmentYear;
    public $gradYear;

    function __construct($schoolName, $degreeType, $degreeMajor, $enrollmentYear, $gradYear) {
        $this->schoolName = $schoolName;
        $this->degreeType = $degreeType;
        $this->degreeMajor = $degreeMajor;
        $this->enrollmentYear = $enrollmentYear;
        $this->gradYear = $gradYear;
    }

}

class WorkHistoryEntry {
    public $companyName;
    public $jobTitle;
    public $startYear;
    public $endYear;

    function __construct($companyName, $jobTitle, $startYear, $endYear) {
        $this->companyName = $companyName;
        $this->jobTitle = $jobTitle;
        $this->startYear = $startYear;
        $this->endYear = $endYear;
    }
}

class  Input {
    static $errors = false;

    static function check($arr, $on = false) {
        if ($on === false) {
            $on = $_REQUEST;
        }
        foreach ($arr as $value) {
            if (empty($on[$value])) {
                self::throwError('Data is missing', 900);
            }
        }
    }

    static function int($val) {
        $val = filter_var($val, FILTER_VALIDATE_INT);
        if ($val === false) {
            self::throwError('Invalid Integer', 901);
        }
        return $val;
    }

    static function str($val) {
        if (!is_string($val)) {
            self::throwError('Invalid String', 902);
        }
        $val = trim(htmlspecialchars($val));
        return $val;
    }

    static function bool($val) {
        $val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
        return $val;
    }

    static function email($val) {
        $val = filter_var($val, FILTER_VALIDATE_EMAIL);
        if ($val === false) {
            self::throwError('Invalid Email', 903);
        }
        return $val;
    }

    static function url($val) {
        $val = filter_var($val, FILTER_VALIDATE_URL);
        if ($val === false) {
            self::throwError('Invalid URL', 904);
        }
        return $val;
    }

    static function throwError($error = 'Error In Processing', $errorCode = 0) {
        if (self::$errors === true) {
            throw new Exception($error, $errorCode);
        }
    }
}
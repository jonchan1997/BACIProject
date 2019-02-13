<?php
    require_once "database.php";

    require_once "session.php";

    if($type < 2){
        header("Location:index.php");
        die;
    }
    if(isset($_POST['add'])){
        $countryName = trim($_POST['addCountry']);

        $report = addCountry($countryName);
        $_SESSION['title'] = $report->title;
        $_SESSION['msg'] = $report->msg;
        $_SESSION['nextModal'] = $report->nextModal;
        $_SESSION['success'] = $report->success;
        $_SESSION['inputs'] = $report->inputs;
        //$_SESSION['report'] = serialize($report);
        header('Location: index.php');
        die;
    } elseif (isset($_POST['edit'])) {
        $id = trim($_POST['country']);
        $newName = trim($_POST['editCountry']);

        $report = editCountry($id, $newName);
        $_SESSION['title'] = $report->title;
        $_SESSION['msg'] = $report->msg;
        $_SESSION['nextModal'] = $report->nextModal;
        $_SESSION['success'] = $report->success;
        $_SESSION['inputs'] = $report->inputs;
        //$_SESSION['report'] = serialize($report);
        header('Location: index.php');
        die;
    } elseif (isset($_POST['delete'])) {
        $countryID = trim($_POST['country']);

        $report = deleteCountry($countryID);
        $_SESSION['title'] = $report->title;
        $_SESSION['msg'] = $report->msg;
        $_SESSION['nextModal'] = $report->nextModal;
        $_SESSION['success'] = $report->success;
        $_SESSION['inputs'] = $report->inputs;
        //$_SESSION['report'] = serialize($report);
        header('Location: index.php');
        die;
    }
    else if(isset($_SESSION['report'])){
        unset($_SESSION['report']);
    }
?>

<div id="addCountryModal" class="w3-modal">
    <div class="w3-modal-content w3-animate-top w3-card-4">
        <header class="w3-container w3-lime w3-center w3-padding-32">
            <span onclick="document.getElementById('addCountryModal').style.display='none'" class="w3-button w3-lime w3-xlarge w3-display-topright">×</span>
            <h2 class="w3-wide"><i class="w3-margin-right"></i>Add Country </h2>
        </header>
        <form method = 'post' action="addCountry.php" class="w3-container">
            <h1>Add new Country</h1>
            <p>
                <label>Country Name</label>
            </p>
            <input class="w3-input w3-border" type="text" maxlength="50" value="" name="addCountry" id="addCountry" />

            <button class="w3-button w3-block w3-lime w3-padding-16 w3-section w3-right" type = "submit" name = "add">Add Country
                <i class="fa fa-check"></i>
            </button>
        </form>

        <form method = 'post' action = "addCountry.php" class = "w3-container">
            <h1>Edit a Country</h1>
            <p>
                <label>Select Country to Edit</label>
            </p>

            <select class="w3-select w3-border" name="country">
                <?php print listCountries(); ?>
            </select>

            <p>
                <label>Enter New Country Name</label>
            </p>
            <input class="w3-input w3-border" type="text" maxlength="50" value="" name="editCountry" id="editCountry" />

            <button class="w3-button w3-block w3-lime w3-padding-16 w3-section w3-right" type="submit" name="edit">Edit
                <i class="fa fa-check"></i>
            </button>

            <p>
                <label>Or Delete Selected Country</label>
            </p>
            <button class="w3-button w3-red w3-section" type = "submit" name = "delete">Disable
                <i class="fa fa-remove"></i>
            </button>



        </form>
    </div>
</div>

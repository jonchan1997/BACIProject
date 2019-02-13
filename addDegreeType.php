<?php
    require_once "database.php";

    require_once "session.php";

    if($type < 2){
        header("Location:index.php");
        die;
    }

    if(isset($_POST['add'])){
        $degreeTypeName = trim($_POST['addDegreeType']);

        $report = addDegreeType($degreeTypeName);

        $_SESSION['title'] = $report->title;
        $_SESSION['msg'] = $report->msg;
        $_SESSION['nextModal'] = $report->nextModal;
        $_SESSION['success'] = $report->success;
        $_SESSION['inputs'] = $report->inputs;

        header("Location:index.php");
        die;
    }
    elseif (isset($_POST['edit'])) {
        $degreeTypeID = trim($_POST['degreeType']);
        $newName = trim($_POST['editDegreeType']);

        $report = editDegreeType($degreeTypeID, $newName);

        $_SESSION['title'] = $report->title;
        $_SESSION['msg'] = $report->msg;
        $_SESSION['nextModal'] = $report->nextModal;
        $_SESSION['success'] = $report->success;
        $_SESSION['inputs'] = $report->inputs;

        header("Location:index.php");
        die;
    }
    elseif (isset($_POST['delete'])) {
        $degreeTypeID = trim($_POST['degreeType']);

        $report = deleteDegreeType($degreeTypeID);

        $_SESSION['title'] = $report->title;
        $_SESSION['msg'] = $report->msg;
        $_SESSION['nextModal'] = $report->nextModal;
        $_SESSION['success'] = $report->success;
        $_SESSION['inputs'] = $report->inputs;

        header("Location:index.php");
        die;
    }

?>

<div id="addDegreeModal" class="w3-modal">
    <div class="w3-modal-content w3-animate-top w3-card-4">
        <header class="w3-container w3-lime w3-center w3-padding-32">
            <span onclick="document.getElementById('addDegreeModal').style.display='none'" class="w3-button w3-lime w3-xlarge w3-display-topright">×</span>
            <h2 class="w3-wide"><i class="w3-margin-right"></i>Add Degree </h2>
        </header>
        <form method = 'post' action="addDegreeType.php" class="w3-container">
            <h1>Add new Degree Type</h1>
            <p>
                <label>Degree Type Name</label>
            </p>
            <input class="w3-input w3-border" type="text" maxlength="50" value="" name="addDegreeType" id="addDegreeType" />

            <button class="w3-button w3-block w3-lime w3-padding-16 w3-section w3-right" type = "submit" name = "add">Add DegreeType
                <i class="fa fa-check"></i>
            </button>
        </form>

        <form method = 'post' action = "addDegreeType.php" class = "w3-container">
            <h1>Edit a Degree Type</h1>
            <p>
                <label>Select Degree Type to Edit</label>
            </p>

            <select class="w3-select w3-border" name="degreeType">
                <?php print listDegreeTypes(); ?>
            </select>

            <p>
                <label>Enter New Degree Type Name</label>
            </p>
            <input class="w3-input w3-border" type="text" maxlength="50" value="" name="editDegreeType" id="editDegreeType" />

            <button class="w3-button w3-block w3-lime w3-padding-16 w3-section w3-right" type="submit" name="edit">Edit
                <i class="fa fa-check"></i>
            </button>

            <p>
                <label>Or Delete Selected Degree Type</label>
            </p>
            <button class="w3-button w3-red w3-section" type = "submit" name = "delete">Disable
                <i class="fa fa-remove"></i>
            </button>



        </form>
    </div>
</div>

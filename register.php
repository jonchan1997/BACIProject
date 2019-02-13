<?php
require_once "dbhelper.php";
if(isset($_SESSION['email'])){
    //$_SESSION['email']= "";
}
?>

<script src="js/showStates.js"></script>
<script src="js/registration.js"></script>

<div id="registerModal" class="w3-modal">
    <div class="w3-modal-content w3-animate-top w3-card-4">
        <header class="w3-container w3-lime w3-center w3-padding-32">
            <span onclick="document.getElementById('registerModal').style.display='none'"
                  class="w3-button w3-lime w3-xlarge w3-display-topright">×</span>
            <h2 class="w3-wide"><i class="w3-margin-right"></i>Register </h2>
        </header>
        <form method='post' action="index.php" enctype='multipart/form-data' class="w3-container">
            <p>
                <label>First name<span class="w3-text-red">*</span></label>
            </p>
            <input class="w3-input w3-border" type="text" maxlength="50" value="<?php echo (isset($_SESSION['firstName']) ? $_SESSION['firstName'] : "") ?>" name="firstName" id="firstName" required/>
            <p>
                <label>Middle name</label>
            </p>
            <input class="w3-input w3-border" type="text" maxlength="50" value="<?php echo (isset($_SESSION['middleName']) ? $_SESSION['middleName'] : "") ?>" name="middleName" id="middleName"/>
            <p>
                <label>Last name<span class="w3-text-red">*</span></label>
            </p>
            <input class="w3-input w3-border" type="text" maxlength="50" value="<?php echo (isset($_SESSION['lastName']) ? $_SESSION['lastName'] : "") ?>" name="lastName" id="lastName" required/>
            <p>
                <label>Email<span class="w3-text-red">*</span></label>
            </p>
            <input class="w3-input w3-border" type="text" maxlength="50" value="<?php echo (isset($_SESSION['email']) ? $_SESSION['email'] : "") ?>" name="email" id="email" required/>
            <p>
                <label>User Name<span class="w3-text-red">*</span></label>
            </p>
            <input class="w3-input w3-border" type="text" maxlength="50" value="" name="username" id="username" required/>
            <p>
                <label>Password (Must be longer than 12 characters and contains at least 1 digit)<span class="w3-text-red">*</span></label>
            </p>
            <input class="w3-input w3-border" type="password" maxlength="50" value="" name="password" id="password" required/>
            <p>
                <label>Confirm Password<span class="w3-text-red">*</span></label>
            </p>
            <input class="w3-input w3-border" type="password" maxlength="50" value="" name="confirmedPassword" id="confirmedPassword" required/>
            <p>
                <label>Gender<span class="w3-text-red">*</span></label>
            </p>
            <label>Male<input class="w3-radio w3-border" type="radio" name="gender" value="0" checked="checked"/></label>
            <label>Female<input class="w3-radio w3-border" type="radio" name="gender" value="1"/></label>
            <label>Nonbinary<input class="w3-radio w3-border" type="radio" name="gender" value="2"/></label>

            <p>
                <label>Country<span class="w3-text-red">*</span></label>
            </p>
            <select class="w3-select w3-border" name="country" id="country" onchange="showStates(this.value)">
                <?php echo "<option value= '-1'>Please select a Country</option> " . listCountries(); ?>
            </select>

            <p>
                <label>State/Province<span class="w3-text-red">*</span></label>
            </p>
            <select class="w3-select w3-border" name="state" id="state">
                <option value= '-1'>Please select a State/Province</option>
            </select>

            <p>
                <label>Address Line 1</label>
            </p>
            <input class="w3-input w3-border" type="text" maxlength="50" value="" name="street" id="street"/>
            <p>
                <label>Address Line 2</label>
            </p>
            <input class="w3-input w3-border" type="text" maxlength="50" value="" name="street2" id="street2"/>
            <p>
                <label>City</label>
            </p>
            <input class="w3-input w3-border" type="text" maxlength="50" value="" name="city" id="city"/>
            <p>
                <label>Postal Code</label>
            </p>
            <input class="w3-input w3-border" type="text" maxlength="50" value="" name="postcode" id="postcode"/>

            <p>
                <label>Date of Birth</label>
            </p>
            <input class="w3-input w3-border" type="text" name="dob" id="dob" required>

            <p>
                <label>Phone number<span class="w3-text-red">*</span></label>
            </p>
            <input class="w3-input w3-border" type="tel" value="" name="phoneNumber"/>

            <p>
                <label>Facebook Link</label>
            </p>
            <input class="w3-input w3-border" type="text" maxlength="50" value="" name="facebook" id="facebook"/>
            <p>
                <label>Twitter Link</label>
            </p>
            <input class="w3-input w3-border" type="text" maxlength="50" value="" name="twitter" id="twitter"/>
            <p>
                <label>LinkedIn Link</label>
            </p>
            <input class="w3-input w3-border" type="text" maxlength="50" value="" name="linkedin" id="linkedin"/>

            <p>
                <label>Status<span class="w3-text-red">*</span></label>
            </p>
            <label>Student</label>
            <input class="w3-check w3-border" type="checkbox" name="status" value="0"/>
            <label>Working Professional</label>
            <input class="w3-check w3-border" type="checkbox" name="status" value="1"/>

            <p>
                <label>Preference</label>
            </p>
            <label>Mentor</label>
            <input class="w3-check w3-border" type="checkbox" name="preference" value="0"/>
            <label>Mentee</label>
            <input class="w3-check w3-border" type="checkbox" name="preference" value="1"/>
            <label>Not Interested</label>
            <input class="w3-check w3-border" type="checkbox" name="preference" value="2"/>

            <p>
                <h2>Education History</h2>
            </p>
            <fieldset id="education" style="border:0"></fieldset>
            <input name="addDegreeEntry" class="w3-button w3-block w3-lime w3-padding-16 w3-section w3-right" type="button"
                   value="Add degree..." onclick="addEducationField()"/>
            <input type="hidden" id="numDegs" name="numDegs" value="0">

            <p>
                <h2>Work History</h2>
            </p>
            <fieldset id="work" style="border:0"></fieldset>
            <input name="addJobEntry" class="w3-button w3-block w3-lime w3-padding-16 w3-section w3-right" type="button"
                   value="Add job..." onclick="addWorkField()"/>
            <input type="hidden" id="numJobs" name="numJobs" value="0">

            <p>
                <label>Profile Picture</label>
            </p>
            <input class="w3-input w3-border" type="file" id="profile" name="profile" accept="image/png, image/jpeg" />

            <p>
                <label>Resume</label>
            </p>
            <input class="w3-input w3-border" type="file" id="resume" name="resume" accept=".doc, .docx, .pdf" />

            <button class="w3-button w3-block w3-lime w3-padding-16 w3-section w3-right" type="submit" name="register">
                Register
                <i class="fa fa-check"></i>
            </button>
            <button type="button" class="w3-button w3-red w3-section"
                    onclick="document.getElementById('registerModal').style.display='none'">Close
                <i class="fa fa-remove"></i>
            </button>
        </form>
    </div>
</div>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
    $(function() {
        $("#dob").datepicker({
            changeYear: true,
            dateFormat: "yy-mm-dd",
            yearRange: "1940:2018"
        });
    });
</script>
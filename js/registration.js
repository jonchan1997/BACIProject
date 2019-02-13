function getDegreeList(i) {
    let xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function(){
        if(this.readyState == 4 && this.status == 200){
            document.getElementById('degreeType_' + i).innerHTML = this.responseText;
        }
    };
    xmlhttp.open("GET", "AJAX.php?action=getDegrees", true);
    xmlhttp.send();
}

function removeEducationField(number) {
    document.getElementById("eduContainer_" + number).remove();
    //document.getElementById("eduMember_" + number).remove();
    //document.getElementById("eduBreak_" + number).remove();

    var fieldCount = 0;
    var divs = document.querySelectorAll(".educationMember");
    [].forEach.call(divs, function(div) {
        var newNum = fieldCount.valueOf();
        var oldNumber = div.id.substring(10);
        console.log("old num: " + oldNumber);
        div.id = "eduMember_" + fieldCount;

        var brk = document.getElementById("eduBreak_" + oldNumber);
        brk.id = "eduBreak_" + newNum;

        var cont = document.getElementById("eduContainer_" + oldNumber);
        cont.id = "eduContainer_" + newNum;

        var schoolName = document.getElementById("schoolName_" + oldNumber);
        schoolName.id = "schoolName_" + newNum;
        var majorName = document.getElementById("major_" + oldNumber);
        majorName.id = "major_" + newNum;
        var year = document.getElementById("gradYear_" + oldNumber);
        year.id = "gradYear_" + newNum;
        var button = document.getElementById("eduHeaderSpan_" + oldNumber);
        button.id = "eduHeaderSpan_" + newNum;
        button.onclick = function() {
            removeEducationField(newNum);
        };
        fieldCount = fieldCount + 1;
    });

    document.getElementById("numDegs").value = document.querySelectorAll(".educationMember").length;
}

function addEducationField() {
    // Number of inputs to create
    var number = document.querySelectorAll(".educationMember").length;
    // Container <div> where dynamic content will be placed
    var fieldset = document.getElementById("education");
    var container = document.createElement("div");
    container.id = "eduContainer_" + number;
    container.className = "w3-card w3-display-container w3-margin-top w3-margin-bottom";

    var header = document.createElement("header");
    header.className = "w3-container w3-center w3-pale-red";
    var span = document.createElement("span");
    span.className = "w3-button w3-red w3-xlarge w3-display-topright";
    span.innerHTML = "&times";
    span.id = "eduHeaderSpan_" + number;
    span.onclick = function() {
        removeEducationField(number);
    };
    var label = document.createElement("h3");
    label.innerHTML = "Education Entry";
    header.appendChild(span);
    header.appendChild(label);
    container.appendChild(header);

    // Append a line break
    var brk = document.createElement("br");
    brk.id = "eduBreak_" + number;
    container.appendChild(brk);

    var parent = document.createElement("div");
    parent.className = "educationMember";
    parent.style = "padding: 16px";
    parent.id = "eduMember_" + number;

    var select = document.createElement("select");
    select.name = "degreeType_" + number;
    select.id = "degreeType_" + number;
    select.className = "w3-select w3-border";
    select.innerHTML = '';
    getDegreeList(number);

    parent.appendChild(select);

    parent.appendChild(document.createTextNode("School Name:"));

    var schoolNameInput = document.createElement("input");
    schoolNameInput.type = "text";
    schoolNameInput.maxlength = 50;
    schoolNameInput.name = "schoolName_" + number;
    schoolNameInput.id = "schoolName_" + number;
    schoolNameInput.className = "w3-input w3-border";
    parent.appendChild(schoolNameInput);

    parent.appendChild(document.createTextNode("Major:"));

    var majorInput = document.createElement("input");
    majorInput.type = "text";
    majorInput.maxlength = 50;
    majorInput.name = "major_" + number;
    majorInput.id = "major_" + number;
    majorInput.className = "w3-input w3-border";
    parent.appendChild(majorInput);

    parent.appendChild(document.createTextNode("Year Enrolled:"));

    var startYearInput = document.createElement("input");
    startYearInput.type = "number";
    startYearInput.maxlength = 4;
    startYearInput.value = "2000";
    startYearInput.name = "enrollmentYear_" + number;
    startYearInput.id = "enrollmentYear_" + number;
    startYearInput.className = "w3-input w3-border";
    parent.appendChild(startYearInput);

    parent.appendChild(document.createTextNode("Year Graduated:"));

    var graduationYearInput = document.createElement("input");
    graduationYearInput.type = "number";
    graduationYearInput.maxlength = 4;
    graduationYearInput.value = "2000";
    graduationYearInput.name = "gradYear_" + number;
    graduationYearInput.id = "gradYear_" + number;
    graduationYearInput.className = "w3-input w3-border w3-margin-bottom";
    parent.appendChild(graduationYearInput);

    container.appendChild(parent);
    fieldset.appendChild(container);

    document.getElementById("numDegs").value = number + 1;
}

function removeWorkField(number) {
    document.getElementById("workContainer_" + number).remove();

    var fieldCount = 0;
    var divs = document.querySelectorAll(".workMember");
    [].forEach.call(divs, function(div) {
        var newNum = fieldCount.valueOf();
        var oldNumber = div.id.substring(11);
        console.log("old num: " + oldNumber);
        div.id = "workMember_" + fieldCount;

        var brk = document.getElementById("workBreak_" + oldNumber);
        brk.id = "workBreak_" + newNum;

        var cont = document.getElementById("workContainer_" + oldNumber);
        cont.id = "workContainer_" + newNum;

        var employerName = document.getElementById("employerName_" + oldNumber);
        employerName.id = "employerName_" + newNum;

        var jobTitle = document.getElementById("jobTitle_" + oldNumber);
        jobTitle.id = "jobTitle_" + newNum;

        var startYear = document.getElementById("startYear_" + oldNumber);
        startYear.id = "startYear_" + newNum;

        var endYear = document.getElementById("endYear_" + oldNumber);
        endYear.id = "endYear_" + newNum;

        var button = document.getElementById("workHeaderSpan_" + oldNumber);
        button.id = "workHeaderSpan_" + newNum;
        button.onclick = function() {
            removeWorkField(newNum);
        };
        fieldCount = fieldCount + 1;
    });

    document.getElementById("numJobs").value = document.querySelectorAll(".workMember").length;
}

function addWorkField() {
    // Number of inputs to create
    var number = document.querySelectorAll(".workMember").length;
    // Container <div> where dynamic content will be placed
    var fieldset = document.getElementById("work");
    var container = document.createElement("div");
    container.id = "workContainer_" + number;
    container.className = "w3-card w3-display-container w3-margin-top w3-margin-bottom";

    var header = document.createElement("header");
    header.className = "w3-container w3-center w3-pale-red";
    var span = document.createElement("span");
    span.className = "w3-button w3-red w3-xlarge w3-display-topright";
    span.innerHTML = "&times";
    span.id = "workHeaderSpan_" + number;
    span.onclick = function() {
        removeWorkField(number);
    };
    var label = document.createElement("h3");
    label.innerHTML = "Job Entry";
    header.appendChild(span);
    header.appendChild(label);
    container.appendChild(header);

    // Append a line break
    var brk = document.createElement("br");
    brk.id = "workBreak_" + number;
    container.appendChild(brk);

    var parent = document.createElement("div");
    parent.className = "workMember";
    parent.style = "padding: 16px";
    parent.id = "workMember_" + number;

    parent.appendChild(document.createTextNode("Name of Employer:"));

    var employerNameInput = document.createElement("input");
    employerNameInput.type = "text";
    employerNameInput.maxlength = 50;
    employerNameInput.name = "employerName_" + number;
    employerNameInput.id = "employerName_" + number;
    employerNameInput.className = "w3-input w3-border";
    parent.appendChild(employerNameInput);

    parent.appendChild(document.createTextNode("Job Title:"));

    var jobTitle = document.createElement("input");
    jobTitle.type = "text";
    jobTitle.maxlength = 50;
    jobTitle.name = "jobTitle_" + number;
    jobTitle.id = "jobTitle_" + number;
    jobTitle.className = "w3-input w3-border";
    parent.appendChild(jobTitle);

    parent.appendChild(document.createTextNode("Year Started:"));

    var startYearInput = document.createElement("input");
    startYearInput.type = "number";
    startYearInput.maxlength = 4;
    startYearInput.value = "2000";
    startYearInput.name = "startYear_" + number;
    startYearInput.id = "startYear_" + number;
    startYearInput.className = "w3-input w3-border";
    parent.appendChild(startYearInput);

    parent.appendChild(document.createTextNode("Year Ended:"));

    var endYearInput = document.createElement("input");
    endYearInput.type = "number";
    endYearInput.maxlength = 4;
    endYearInput.value = "2000";
    endYearInput.name = "endYear_" + number;
    endYearInput.id = "endYear_" + number;
    endYearInput.className = "w3-input w3-border w3-margin-bottom";
    parent.appendChild(endYearInput);

    container.appendChild(parent);
    fieldset.appendChild(container);

    document.getElementById("numJobs").value = number + 1;
}

function init() {
    addEducationField();
    addWorkField();
}
function showStates(countryID) {
    let elementID = "state";
    if(countryID != ""){
        let xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function(){
            if(this.readyState == 4 && this.status == 200){
                document.getElementById(elementID).innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET", "AJAX.php?action=refreshState&country=" + countryID, true);
        xmlhttp.send();
    }
}
var offset = 0;

function continuallyLoadCards(num, pref) {
    let xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            cardAjax(JSON.parse(this.responseText));
        }
    };
    xmlhttp.open("POST", "search.php", true);
    xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    if (pref == -1) {
        xmlhttp.send("action=loadCards&num=" + num +"&offset=" + offset);
    } else {
        xmlhttp.send("action=loadCards&num=" + num +"&offset=" + offset + "&pref=" + pref);
    }
    offset += num;
}

function searchCards(num, startOver, pref) {
    if (startOver) {
        document.getElementById("cardDisplay").innerHTML = "";
        offset = 0;
    }

    let term = document.getElementById("searchBox").value;
    if (term === "") {
        continuallyLoadCards(30, pref);
        return;
    }

    let xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            cardAjax(JSON.parse(this.responseText));
        }
    };

    xmlhttp.open("POST", "search.php", true);
    xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    if (pref == -1) {
        xmlhttp.send("action=loadCards&num=" + num +"&offset=" + offset + "&search=" + term);
    } else {
        xmlhttp.send("action=loadCards&num=" + num +"&offset=" + offset + "&search=" + term + "&pref=" + pref);
    }
    offset += num;
}
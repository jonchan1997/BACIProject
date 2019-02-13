function cardAjax(ids) {
    ids.forEach(function(id) {
        if (!$('#cardDisplay').find('#' + id["account_ID"]).length) {
            let cached = sessionStorage.getItem(id["account_ID"]);
            if (cached != null) {
                document.getElementById("cardDisplay").innerHTML += cached;
                imageAjax(id);
            } else {
                let xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        sessionStorage.setItem(id["account_ID"], this.responseText);
                        document.getElementById("cardDisplay").innerHTML += this.responseText;
                        imageAjax(id);
                    }
                };
                xmlhttp.open("GET", "card.php?id=" + id["account_ID"], true);
                xmlhttp.send();
            }
        }
    });
}

function imageAjax(id) {

    let cached = sessionStorage.getItem(id["account_ID"] + "_img");
    if (cached != null) {
        document.getElementById(id["account_ID"]).src = cached;
    } else {
        let xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                let elementID = id["account_ID"];
                sessionStorage.setItem(elementID + "_img", this.responseText);
                document.getElementById(elementID).src = this.responseText;
            }
        };
        xmlhttp.open("GET", "image.php?account_id=" + id["account_ID"], true);
        xmlhttp.send();
    }
}
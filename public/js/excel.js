
function loadXls() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            //ответ сервера
            var response = JSON.parse(this.responseText);
            if (
                (response === false)
                || (typeof response.fileContents === "undefined")
                || (typeof response.filename === "undefined")
            ) {
                //error text is send from server in json
                var errorText = response.error;
                alert(errorText);
            } else {
                var datauri = 'data:application/vnd.ms-excel;base64,' + response.fileContents;
                saveAs(datauri, response.filename);
            }
        }
    };
    var get = '?xls';
    xhttp.open("GET", "info.php" + get);
    xhttp.send();
}

function xlsToEmail() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            //ответ сервера
            var response = JSON.parse(this.responseText);
            if (
                (response === false)) {
                var errorText = 'Не удалось отправить документ на вашу почту.';
                alert(errorText);
            } else if(response.hasOwnProperty('error')) {
                errorText = response.error;
                alert(errorText);
            } else {
                var successText = 'Документ отослан по адресу: ' + response;
                alert(successText);
            }
        }
    };
    var get = '?xlsToMail';
    xhttp.open("GET", "info.php" + get);
    xhttp.send();
}

//https://stackoverflow.com/a/25715985
function saveAs(uri, filename) {
    var link = document.createElement('a');
    if (typeof link.download === 'string') {
        link.href = uri;
        link.download = filename;

        //Firefox requires the link to be in the body
        document.body.appendChild(link);

        //simulate click
        link.click();

        //remove the link when done
        document.body.removeChild(link);
    } else {
        window.open(uri);
    }
}


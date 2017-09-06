

function registerCargo() {
    var container = document.getElementById('container_name').value;
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            //ответ сервера
            var response = JSON.parse(this.responseText);
            if (response === false) {
                var errorText = 'Извините, не получилось зарегистрировать груз.';
                alert(errorText);
            } else if(response.hasOwnProperty('error')) {
                errorText = response.error;
                alert(errorText);
            } else {
                var text = 'Груз добавлен под ID = ' + response + '. Обновите страницу, чтобы увидеть его.';
                closeCargoDialog();
                alert(text);
            }
        }
    };
    var params = 'container=' + encodeURIComponent(container);
    xhttp.open("POST", "newcargo.php");
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(params);
}

/**
 * Make page snippet visible.
 */
function openCargoDialog(){
    $("#cargo_dialog").fadeIn(); //плавное появление блока
}

/**
 * Make page snippet invisible.
 */
function closeCargoDialog(){
    $("#cargo_dialog").fadeOut(); //плавное исчезание блока
}
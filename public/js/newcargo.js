/**
 * Allow client to register new cargo.
 */
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
    xhttp.open("POST", "new_cargo.php");
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

/**
 * Make manager executor of cargo order.
 * @param cargoID
 */
function makeExecutor(cargoID) {
    $.ajax({
        type: 'post',
        url: 'awaiting_list.php',
        data: {'makeExecutor' : true, 'cargoID' : cargoID},
        success: function (response, status, xhr) {
            response = JSON.parse(response);
            if (response === false) {
                var errorText = 'Не получилось изменить менеджера груза.';
                alert(errorText);
            } else if(response.hasOwnProperty('error')) {
                errorText = response.error;
                alert(errorText);
            } else {
                var text = 'Груз теперь под вашим надзором, обновите страницу чтобы увидеть изменения.';
                closeEditDialog();
                alert(text);
            }
        }
    });
}
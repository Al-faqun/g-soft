$(document).ready(function() {
        $("#edit_form").on('submit', function (event) {
            event.stopPropagation();
            event.preventDefault();
            $.ajax({
                type: 'post',
                url: 'editCargo.php',
                data: $('#edit_form').serialize(),
                success: function (response, status, xhr) {
                    response = JSON.parse(response);
                    if (response === false) {
                        var errorText = 'Не получилось изменить данные груза.';
                        alert(errorText);
                    } else if(response.hasOwnProperty('error')) {
                        errorText = response.error;
                        alert(errorText);
                    } else {
                        var text = 'Данные груза изменены, обновите страницу чтобы увидеть изменения.';
                        closeEditDialog();
                        alert(text);
                    }
                }
            });
        });

    //color of clientname and managername <td> cells to trick attention
    $("td[id*='clientname']").hover(
        function () {
            $(this).css("background-color",'#f9ffbf');
        },
        function () {
            $(this).css("background-color",'white');
        });
    $("td[id*='manname']").hover(
        function () {
            $(this).css("background-color",'#ffebfc');
        },
        function () {
            $(this).css("background-color",'white');
        });
    });

/**
 * Make page snippet visible.
 */
function openEditDialog(cargoID){
    saveCargoIdForJs(cargoID);
    //set default value of input from cargo table
    $("#date_arrival")[0].value = $("#cargo_" + cargoID + "_datearrival")[0].innerHTML;
    $("#edit_cargo_dialog").fadeIn(); //плавное появление блока
}

/**
 * Make page snippet invisible.
 */
function closeEditDialog(){
    $("#edit_cargo_dialog").fadeOut(); //плавное исчезание блока
}

function saveCargoIdForJs(cargoID)
{
    document.getElementById('cargo_id').value = cargoID;
}
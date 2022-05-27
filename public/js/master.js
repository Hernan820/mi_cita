var principalUrl = "http://localhost/mi/public/";

$(document).ready(function () {
    var id = $("#id_cupo").val();
    var rol = $("#rol").val();
    var user = $("#user").val();


    $("#grid").DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
        },
        lengthChange: false,
        pageLength: 20,
        bInfo: false,
        order: [[0, "desc"]],
        ajax: {
            url: principalUrl + "cliente/cita/" + 0,
            dataSrc: "",
        },
        columns: [
            { data: "hora", width: "50px" },
            { data: "nombrec", width: "75px" },
            { data: "telefono" },
            {
                data: "descripcion",
                width: "200px",

                render: function (data, type, full) {
                    var showChar = 80;
                    var ellipsestext = "...";
                    var moretext = "ver mas";
                    var contentt = JSON.stringify(data);
                    var texto = contentt.replace(/\\r\\n/g, "<br />");
                    var content = texto
                        .replace(/["]+/g, "")
                        .substring(0, texto.length - 1);

                    if (content.length > showChar) {
                        var c = content.substr(0, showChar);
                        var h = content.substr(
                            showChar,
                            content.length - showChar
                        );

                        var html =
                            c +
                            '<span class="moreellipses">' +
                            ellipsestext +
                            '</span><span ><span class="morecontent">' +
                            h +
                            '</span>&nbsp;&nbsp;<a onclick="read(this)" class="morelink " >' +
                            moretext +
                            "</a></span>";
                        return html.toString();
                    }
                    return data;
                },
            },
            { data: "name", width: "50px" },
            {
                data: "id_estado",
                width: "50px",
                render: function (data, nRow) {
                    return data != 1
                        ? data != 2
                            ? data != 3
                                ? data != 4
                                    ? data == 5
                                        ? '<img class="center-icon" src="' +
                                          principalUrl +
                                          'iconos/telefono.png">'
                                        : '<img class="center-icon" src="' +
                                          principalUrl +
                                          'iconos/telefono.png">'
                                    : '<img class="center-icon" src="' +
                                      principalUrl +
                                      'iconos/reloj.png">'
                                : '<img class="center-icon" src="' +
                                  principalUrl +
                                  'iconos/reagenda.png">'
                            : '<img class="center-icon" src="' +
                              principalUrl +
                              'iconos/botonx.png">'
                        : '<td ><img class="center-icon" src="' +
                              principalUrl +
                              'iconos/confirmado.png"></td>';
                },
            },

            { data: "motivo_cancelacion", width: "175px" },
            {
                data: "confirmacion",
                width: "25px",
                render: function (data, nRow) {
                    return (data =
                        data != 1
                            ? data != 0
                                ? "<p></p>"
                                : "<p>No</p>"
                            : "<p>Si</p>");
                },
            },
            {
                data: "id",
                width: "100px",
                render: function (data, type, row) {
                    var id_user = row["id_usuario"];
                    var estado = row["id_estado"];

                    if (estado != 3) {
                        if (rol == "administrador") {
                            return (
                                '<select id="acciones" class="form-control opciones" onchange="editar(this,' +
                                data +
                                ')"><option selected="selected" disabled selected>Acciones</option><option value="1">Editar</option><option value="2">Reagendar</option><option value="3">Cancelar</option><option value="4">Confirmar cita</option><option value="5">Nota</option><option value="6">Asistencia</option><option value="7">Eliminar</option><option value="8">No answer</option></selec>'
                            );
                        } else if (rol == "usuario") {
                            return (
                                '<select id="acciones" class="form-control opciones" onchange="editar(this,' +
                                data +
                                ')"><option selected="selected" disabled selected>Acciones</option><option value="1">Editar</option><option value="2">Reagendar</option><option value="3">Cancelar</option><option value="4">Confirmar cita</option><option value="5">Nota</option><option value="8">No answer</option></selec>'
                            );
                        } else if (rol == "agente") {
                            if (id_user == user) {
                                return (
                                    '<select id="acciones" class="form-control opciones" onchange="editar(this,' +
                                    data +
                                    ')"><option selected="selected" disabled selected>Acciones</option><option value="1">Editar</option><option value="2">Reagendar</option><option value="3">Cancelar</option><option value="4">Confirmar cita</option><option value="5">Nota</option><option value="7">Eliminar</option><option value="8">No answer</option></selec>'
                                );
                            } else {
                                return (
                                    '<select id="acciones" class="form-control opciones" onchange="editar(this,' +
                                    data +
                                    ')"><option selected="selected" disabled selected>Acciones</option><option value="5">Nota</option></selec>'
                                );
                            }
                        }
                    } else {
                        return (
                            '<select id="acciones" class="form-control opciones" onchange="editar(this,' +
                            data +
                            ')"><option selected="selected" disabled selected>Acciones</option></selec>'
                        );
                    }
                },
            },
        ],
        columnDefs: [
            {
                type: "date",
                render: function (data, type, row, meta) {
                    return (data = moment(data, "hh:mm A").format("hh:mm A"));
                },
                targets: 0,
            },

            {
                targets: 1, //column index
                data: "Nombre cliente",
                render: function (data, type, row) {
                    return data + " " + row["apellidos"];
                },
            },
            {
                aTargets: [5],
                fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                    if (sData == 1) {
                        $(nTd)
                            .css("background-color", "#00FE1F")
                            .css("color", "#4F8A10")
                            .css("font-weight", "bold")
                            .css("text-align", "center");
                    } else if (sData == 2) {
                        $(nTd)
                            .css("background-color", "#FE2300")
                            .css("color", "#4F8A10")
                            .css("font-weight", "bold")
                            .css("text-align", "center");
                    } else if (sData == 3) {
                        $(nTd)
                            .css("background-color", "#F600FE")
                            .css("color", "#4F8A10")
                            .css("font-weight", "bold")
                            .css("text-align", "center");
                    } else if (sData == 4) {
                        $(nTd)
                            .css("background-color", "#FEE700")
                            .css("color", "#4F8A10")
                            .css("font-weight", "bold")
                            .css("text-align", "center");
                    } else if (sData == 5) {
                        $(nTd)
                            .css("background-color", "#F88503")
                            .css("color", "#4F8A10")
                            .css("font-weight", "bold")
                            .css("text-align", "center");
                    }
                },
            },
        ],
    });
    document.getElementById("descripcion").value = "texthere\\\ntexttext";

    horaCupo(id);
});

function read(obj) {
    if ($(obj).hasClass("menos")) {
        $(obj).removeClass("menos");
        $(obj).html("ver mas");
    } else {
        $(obj).addClass("menos");
        $(obj).html("ver menos");
    }
    $(obj).parent().prev().toggle();
    $(obj).prev().toggle();
    return false;
}
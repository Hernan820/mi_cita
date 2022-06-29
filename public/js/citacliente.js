var principalUrl = "http://localhost/mi_cita/public/";

let formCancelar = document.getElementById("cancelarCita");
let formreagendar = document.getElementById("reagendarform");


    document.getElementById("confirmar").addEventListener("click", function () {

        var idcita = $("#idcita").val();
        var nombre = $("#nombre").val();
        var apellidos = $("#apellidos").val();
        var oficina = $("#nombreoficina").val();
        var fecha = $("#fechac").val();

     if( $('#estadocita').val() == "confirmado"){

        Swal.fire({
            position: "top-end",
            icon: "success",
            title: "¡Registro ya esta confirmado !",
            showConfirmButton: false,
            timer: 1500
        }); 
     }else {
        
    Swal.fire({
        title: "Confirmar cita",
        text: "¿Estas seguro de confirmar tu cita "+nombre+" "+apellidos+" para la fecha: "+fecha+", en la oficina de "+oficina+" ?",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "SI",
        cancelButtonText: "NO",
    }).then((result) => {
        if (result.isConfirmed) {

            $('#confirmar').attr('disabled', true);

    
            axios.post(principalUrl + "cliente/confirmar/"+idcita)
            .then((respuesta) => {
                $('#confirmar').attr('disabled', false);

                if(respuesta.data == 1){
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "¡Registro confirmado exitosamente!",
                        showConfirmButton: false,
                    }); 
                }
                location.reload();
            }).catch((error) => {
                if (error.response) {
                    console.log(error.response.data);
                }
            });
        } else {
        }
    });
   }
    });


    document.getElementById("btnCancelar").addEventListener("click", function () {
        if (validardatos() == false) { return;}

        var datos = new FormData(formCancelar);
        var idcita = $("#idcita").val();
        var nombre = $("#nombre").val();
        var apellidos = $("#apellidos").val();

    Swal.fire({
        title: "Cancelar cita",
        text: "¿Estas seguro de cancelar tu cita "+nombre+" "+apellidos+" ?",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "SI",
        cancelButtonText: "NO",
    }).then((result) => {
        if (result.isConfirmed) {

            $('#btnCancelar').attr('disabled', true);

    
           axios.post(principalUrl + "cliente/cancelar/"+idcita , datos )
            .then((respuesta) => {

              $('#btnCancelar').attr('disabled', false);

        
                if(respuesta.data == 1){
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "¡Cita cancelada exitosamente!",
                        showConfirmButton: false,
                    }); 
                }
              location.reload(); 
            }).catch((error) => {
                if (error.response) {
                    console.log(error.response.data);
                }
            });
        } else {
        }
    });
});


    function validardatos() {
        var valido = true;    
        var motivo = $("#motivo").val();

        if (motivo === "" ) {
            Swal.fire("¡Debe ingresar un motivo, para cancelar su cita!");
            valido = false;
        }
        return valido;
    }

    $('#cancelar').on('click', function() {

        if( $('#estadocita').val() == "cancelado"){
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: "¡Cita ya ha sido  cancelada exitosamente!",
                showConfirmButton: false,
                timer: 1500
            }); 
        }else {
        $("#cancelarCita").trigger("reset");
        $("#popup_cancelar").modal("show");
        $("#motivo").focus();
        }
    });



    $('#reagendar').on('click', function() {

        $("#cuposid").html("");
        var cupoid = $("#idcupo").val();

        axios.post(principalUrl + "cliente/cupos")
        .then((respuesta) => {
            moment.locale("es");
            $("#cuposid").append( "<option enabled='true' value=''>Elige una Fecha &nbsp;&nbsp;&nbsp;&nbsp; Oficina</option>");

                respuesta.data.forEach(function (element) {   
                    if(element.id != cupoid){                                 
                    $("#cuposid").append("<option value=" +element.id+">"+moment(element.start).utc().locale('es').format("dddd DD [de] MMMM [del] YYYY")+" ---------- "+element.nombre+"</option>");
                    }
                });

            if(respuesta.data == 1){
                Swal.fire({
                    position: "top-end",
                    icon: "success",
                    title: "¡Cita cancelada exitosamente!",
                    showConfirmButton: false,
                }); 
            }
        }).catch((error) => {
            if (error.response) {
                console.log(error.response.data);
            }
        });

        $(".hora").val("");
        $("#reagendar").trigger("reset");
        $("#popup_reagendar").modal("show");
    });

    $('#cuposid').on('change', function() {
        
        $(".hora").val("");
        var id = $("#cuposid").val();
        $("#horaReagendar").html("");
    axios.post(principalUrl + "cita/listarHorario/"+id)
        .then((respuesta) => { 

            $("#nombreofic").val(respuesta.data.cantCitas.nombreoficina);
            $("#num_citas").val(respuesta.data.cantCitas.cant_citas);
            $("#fecharea").val( moment(respuesta.data.cantCitas.start).utc().locale('es').format("dddd DD [de] MMMM [del] YYYY"));
           
            $("#horaReagendar").append(
                "<option enabled selected value=''>Horas</option>"
                );

                var horasvacias = 0;
                respuesta.data.hora.forEach(function (element) {
                    if(element.total00 < element.cant_citas || element.total30 < element.cant_citas ){
                        $("#horaReagendar").append("<option value=" +element.hora24 +">"+element.hora12+"</option>");
                        horasvacias++;
                    }
                });

                if(horasvacias == 0){
                    $("#horaReagendar").append("<option value=''>No hay horas vacias</option>");
                }
        })
        .catch((error) => {
            if (error.response) {
                console.log(error.response.data);
            }
        }); 
    });


 $('#horaReagendar').on('change', function() {
    $('#minutosReagendar option[value="00"]').attr("disabled", false);
    $('#minutosReagendar option[value="30"]').attr("disabled", false);
    $("#minutosReagendar").val("00");

        var hora = $("#horaReagendar").val();
        var id = $("#cuposid").val();

    axios.post(principalUrl + "cita/listarHorario/"+id)
        .then((respuesta) => { 
            respuesta.data.hora.forEach(function (element) {
                if(element.hora24 == hora){

                    if(element.total00 >= element.cant_citas){
                        $('#minutosReagendar option[value="00"]').attr("disabled", true);
                        $("#minutosReagendar").val("30");
                    }

                    if(element.total30 >= element.cant_citas){
                        $('#minutosReagendar option[value="30"]').attr("disabled", true);
                        $("#minutosReagendar").val("00");
                    }
                }
            });
        })
        .catch((error) => {
            if (error.response) {
                console.log(error.response.data);
            }
        });

        if(hora >= 7 && hora <= 11){
            $("#horarioreagenda").val("AM");
        }else if(hora >= 12 && hora <= 20){
            $("#horarioreagenda").val("PM");

        }
});


document.getElementById("btnReagendar").addEventListener("click", function () {
    var cupo = $("#idcupo").val();
    var hora = $("#horaReagendar").val();
    var oficina = $("#nombreofic").val();
    var fecha = $("#fecharea").val();


    if (cupo === "" || hora === "" ) {
        Swal.fire("¡Debe llenar todos los datos requeridos!");
        return;
    }


    Swal.fire({
        title: "Reagendar cita",
        text: "¿Estas seguro de reagendar cita para la fecha: "+fecha+", en la oficina: "+oficina+" ?",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "SI",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {

            var reagendaCita = new FormData(formreagendar);

            $('#btnReagendar').attr('disabled', true);


            axios.post(principalUrl + "cita/reagendar", reagendaCita)
                .then((respuesta) => {
                    $('#btnReagendar').attr('disabled', false);

                    if(respuesta.data == 1){

                        Swal.fire({
                            position: "top-end",
                            icon: "success",
                            title: "Cita Reagendada exitosamente!",
                            showConfirmButton: false,
                        });

                    }
                    location.reload();

                })
                .catch((error) => {
                    if (error.response) {
                        console.log(error.response.data);
                    }
                });
        } else {
            $("#popup_reagendar").modal("hide");
        }
    });

});


$(document).ready(function () {

    if( $('#estadocita').val() == "cancelado"){

        $(`#tarjetacita`).addClass(' border-danger');


    }else if( $('#estadocita').val() == "confirmado" ){

        $(`#tarjetacita`).addClass(' border-success');

    }else if( $('#estadocita').val() == "pendiente"){

        $(`#tarjetacita`).addClass(' border-warning');

    }

});



var principalUrl = "http://localhost/mi_cita/public/";

//var principalUrl = "https://clientes.dailyappsetter.com/";

let formCancelar = document.getElementById("cancelarCita");
let formreagendar = document.getElementById("reagendarform");
let tipovista = $('#tipecita').val();

function mostrarAnimacion(mensaje_noti) {
    let timerInterval;
    Swal.fire({
      title: mensaje_noti,
      //html: "I will close in <b></b> milliseconds.",
      timerProgressBar: true,
      didOpen: () => {
        Swal.showLoading();
        const timer = Swal.getPopup().querySelector("b");
        timerInterval = setInterval(() => {
          timer.textContent = `${Swal.getTimerLeft()}`;
        }, 100);
      },
      willClose: () => {
        clearInterval(timerInterval);
      }
    });
}

    document.getElementById("confirmar").addEventListener("click", function () {

        var nombre = $("#nombre").val();
        var apellidos = $("#apellidos").val();
        var oficina = $("#nombreoficina").val();
        var fecha = $("#fechac").val();

     if( $('#estadocita').val() == "CONFIRMADA"){

        Swal.fire({
            position: "top-end",
            icon: "success",
            title: "¡Tu cita ya esta confirmada!",
            showConfirmButton: false,
            timer: 1500
        }); 
     }else {
        
    Swal.fire({
        title: "Confirmar cita",
        text: "¿Estás seguro de confirmar tu cita "+nombre+" "+apellidos+" para la fecha: "+fecha+", en la oficina de "+oficina+"?",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "SI",
        cancelButtonText: "NO",
    }).then((result) => {
        if (result.isConfirmed) {

            $('#confirmar').attr('disabled', true);

            var datosconfirm = new FormData();
            datosconfirm.append("vista",tipovista );
            datosconfirm.append("idcita",$("#idcita").val());
            mostrarAnimacion("Confirmando Cita");
    
            axios.post(principalUrl + "cliente/confirmar",datosconfirm)
            .then((respuesta) => {
                $('#confirmar').attr('disabled', false);
                Swal.close();
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

        var datoscancel = new FormData(formCancelar);
        datoscancel.append("vista",tipovista );
        datoscancel.append("idcita",$("#idcita").val());
        var nombre = $("#nombre").val();
        var apellidos = $("#apellidos").val();

    Swal.fire({
        title: "Cancelar cita",
        text: "¿Estás seguro de cancelar tu cita "+nombre+" "+apellidos+"?",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "SI",
        cancelButtonText: "NO",
    }).then((result) => {
        if (result.isConfirmed) {
            mostrarAnimacion("Cancelando Cita");
            $('#btnCancelar').attr('disabled', true);

           axios.post(principalUrl + "cliente/cancelar" , datoscancel )
            .then((respuesta) => {
                Swal.close();
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

        if( $('#estadocita').val() == "CANCELADA"){
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: "¡Cita ya ha sido cancelada exitosamente!",
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
        $("#fechascupos").html("");
        $("#oficinas").html("");
        var ofici = $("#nombreoficina").val();
        var fecha= moment($("#fechacita").val()).utc().locale('es').format("YYYY-MM-DD"); 

        var datoscita = new FormData();
        datoscita.append("vista",tipovista );
        datoscita.append("idcita",$("#idcita").val());

        axios.post(principalUrl + "cliente/oficinas",datoscita)
        .then((respuesta) => {
            moment.locale("es");
            $("#oficinas").append( "<option selected  disabled='true'  value=''>Elige una Oficina</option>");
            $("#fechascupos").append("<option selected disabled='true' value=''>Fechas</option>");

                respuesta.data.ofi.forEach(function (element) {  
                    
                    if (tipovista == "virtual") {
                        $("#oficinas").append("<option selected value='oficina_virtual' >"+element.nombre+"</option>");
                    }else{
                        if(element.nombre == ofici){     
                            $("#oficinas").append("<option selected value='"+element.id+"' >"+element.nombre+"</option>");
                        }else{
                            $("#oficinas").append("<option value='"+element.id+"' >"+element.nombre+"</option>");
                        }
                    }
                });

                if(tipovista === "fisica"){
                $("#oficinas").append( "<option  value='oficina_virtual'> Cita Virtual  <b>( Llamada )</b> </option>");
                }else if(tipovista === "virtual"){

                respuesta.data.ofifisica.forEach(function (element) {   
                    $("#oficinas").append("<option value=" +element.id+">( Oficina física ) "+element.nombre+" </option>");
                });

                }

               if(respuesta.data.cupos.length > 0 ){
                respuesta.data.cupos.forEach(function (element) {  
                    var fecha2 = moment(element.start).utc().locale('es').format("YYYY-MM-DD");
                    if(fecha != fecha2){
                        $("#fechascupos").append("<option  value=" +element.id+">"+moment(element.start).utc().locale('es').format("dddd DD [de] MMMM [del] YYYY")+"</option>");
                    } 
                });

               }else{
                $("#fechascupos").append("<option disabled='true' value=''>No hay mas fechas para esta oficina</option>");
               } 
 
        }).catch((error) => {
            if (error.response) {
                console.log(error.response.data);
            }
        });

        $(".hora").val("");
        $("#horas_cupo").html("");
        $("#reagendar").trigger("reset");
        $("#popup_reagendar").modal("show");
    });

    $('#fechascupos').on('change', function() {
            
        var id_cupo = $(this).val(); 
        var oficina = $("#oficinas").val(); 

        $("#horas_cupo").html("");

        axios.post(principalUrl + "cupos/horario/"+id_cupo+"/"+oficina )
        .then((respuesta) => {

            moment.locale("es");
            $("#horas_cupo").append("<option selected readonly value=''>Horas Cupo</option>");

            respuesta.data.forEach(function (element) {

                if(element.total00 < element.cant_citas ){

                    if(element.hora24.split(':')[0] < 12){
                        $("#horas_cupo").append("<option data-extra='"+element.cant_citas+"' value=" +element.hora24 +">"+element.hora12+" am DISPONIBLE</option>");
                    }else if(element.hora24.split(':')[0] >= 12){
                        $("#horas_cupo").append("<option data-extra='"+element.cant_citas+"' value=" +element.hora24+">"+element.hora12+" pm DISPONIBLE</option>");
                    }

                }else{
                    if(element.hora24.split(':')[0] < 12){
                        $("#horas_cupo").append("<option data-extra='"+element.cant_citas+"' style='background-color:#C5C3C3' disabled value=" +element.hora24 +">"+element.hora12+" am COMPLETO</option>");
                    }else if(element.hora24.split(':')[0] >= 12){
                        $("#horas_cupo").append("<option data-extra='"+element.cant_citas+"' style='background-color:#C5C3C3' disabled value=" +element.hora24+">"+element.hora12+" pm COMPLETO</option>");
                    }
                }

            });
        
        }).catch((error) => {
            if (error.response) {
                console.log(error.response.data);
            }
        });
    });

    document.getElementById('horas_cupo').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var extraValue = selectedOption.getAttribute('data-extra');
        $("#num_citas").val(extraValue);
    });


    $('#oficinas').on('change', function() {
        var idofi= $("#oficinas").val();
        var idcita= $("#idcita").val();

        var datosoficinas = new FormData();

        var oficinaseleccionanda = $("#oficinas option:selected").text();

        if(oficinaseleccionanda !== "Oficina Virtual"){
            datosoficinas.append("vista","fisica" );
        }else{
        datosoficinas.append("vista",tipovista );
        }
        datosoficinas.append("oficinas",$("#oficinas").val() );
        datosoficinas.append("idcita",$("#idcita").val());

        var fecha_de_cita= moment($("#fechacita").val()).utc().locale('es').format("YYYY-MM-DD"); 

        $("#fechascupos").html("");
        $("#horas_cupo").html("");

        axios.post(principalUrl + "cliente/fechasoficinas", datosoficinas )
        .then((respuesta) => {
            moment.locale("es");
            $("#fechascupos").append("<option selected readonly value=''>Fechas</option>");


                if(respuesta.data.length > 0 ){
                    respuesta.data.forEach(function (element) {   

                        var fecha_actual = moment().format('YYYY-MM-DD');
                        var fecha_cupo = moment.utc(element.start).format('YYYY-MM-DD');

                        if(fecha_cupo > fecha_actual && fecha_de_cita != fecha_cupo){
                            $("#fechascupos").append("<option  value=" +element.id+">"+moment(element.start).utc().locale('es').format("dddd DD [de] MMMM [del] YYYY")+"</option>");
                        }
                    });
    
                   }else{
                    $("#fechascupos").append("<option readonly='true'  value=''>No hay mas fechas para esta oficina</option>");
                   } 
        }).catch((error) => {
            if (error.response) {
                console.log(error.response.data);
            }
        });

        $(".hora").val("");
      //  $("#reagendar").trigger("reset");
      //  $("#popup_reagendar").modal("show");
       
    });




 $('#horaReagendar').on('change', function() {
    $('#minutosReagendar option[value="00"]').attr("disabled", false);
    $('#minutosReagendar option[value="30"]').attr("disabled", false);
    $("#minutosReagendar").val("00");

        var hora = $("#horaReagendar").val();

        var datosfechas = new FormData();
        datosfechas.append("vista",tipovista );
        datosfechas.append("idcupo",$("#fechascupos").val());


    axios.post(principalUrl + "cita/listarHorario",datosfechas)
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

    var fechatext = $( "#fechascupos option:selected" ).text();
    var oficinaseleccionanda = $("#oficinas option:selected").text();

    var oficina = $("#oficinas").val();
    var fecha = $("#fechascupos").val();
    var horas = $("#horas_cupo").val();
    var citaid = $("#cita_id").val();
    var cantidadcitas = $("#num_citas").val();

    if (fecha === "" || horas === "" ) {
        Swal.fire("¡Debe llenar todos los datos requeridos!");
        return;
    }

    var reagendaCita = new FormData();
    reagendaCita.append("vista",tipovista );
    reagendaCita.append("Id_oficina",oficina );
    reagendaCita.append("Id_cupo",fecha );
    reagendaCita.append("hora_cita",horas );
    reagendaCita.append("Id_cita",citaid );
    reagendaCita.append("TotalCitasHora",cantidadcitas );


    if(tipovista === "fisica" && $("#oficinas").val() === "oficina_virtual" ){

        Swal.fire({
            title: "Reagendar cita virtual",
            text: "¿Estás seguro de que deseas cambiar tu cita física a una cita virtual para "+fechatext+" ?" ,
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "SI",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                mostrarAnimacion("Reagendando Cita");
                $('#btnReagendar').attr('disabled', true);
                axios.post(principalUrl + "crear/citafisica", reagendaCita)
                    .then((respuesta) => { 
                        Swal.close(); 
                        $('#btnReagendar').attr('disabled', false);
                        if(respuesta.data.validacion == 1){
                            Swal.fire({
                                position: "top-end",
                                icon: "success",
                                title: "Cita Reagendada exitosamente!",
                                showConfirmButton: false,
                            });

                            location.href = principalUrl + "virtual/" + respuesta.data.id_citanueva;

                           // location.reload();
                        }else if(respuesta.data.validacion == 2){
                            Swal.fire({
                                title: '¡Lo siento, no es posible reprogramar la cita, la hora elegida ya está ocupada.!',
                                showDenyButton: false,
                                showCancelButton: false,
                                confirmButtonText: 'Ok, entendido',
                                denyButtonText: false,
                              }).then((result) => {
                                if (result.isConfirmed) {
                                   // location.reload();
                                } else if (result.isDenied) {
                                  Swal.fire('Changes are not saved', '', 'info')
                                }
                              })
                        }else if(respuesta.data.validacion == 55 ){
                            Swal.fire({
                                title: '<strong>Tu cita</strong>',
                                icon: 'error',
                                html:
                                  'Has alcanzado el límite máximo de citas físicas agendadas. Por favor, considere agendar una cita virtual.<br><strong> Si necesitas más ayuda llama al <a href="tel:+1631-609-9108">631-609-9108</a></strong>',
                                showCloseButton: true,
                                showCancelButton: false,
                                focusConfirm: false,
                                confirmButtonText:
                                  'Ok, entendido!',
                                confirmButtonAriaLabel: 'Thumbs up, great!',
                                cancelButtonText: false,
                                cancelButtonAriaLabel: 'Thumbs down'
                              })
                        }else if(respuesta.data.validacion === 35){
                            Swal.fire({
                                position: "top-center",
                                icon: "info",
                                title: "¡Ya existe una cita con tu número de teléfono para la fecha que deseas reagendar tu cita!",
                                showConfirmButton: false,
                            });
                        }
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
    }else if(tipovista === "virtual" && oficinaseleccionanda !== "Oficina Virtual" ){

        Swal.fire({
            title: "Reagendar cita física",
            text: "¿Estás seguro de que deseas cambiar tu cita virtual a una cita física para "+fechatext+" en la "+oficinaseleccionanda+" ?" ,
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "SI",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                mostrarAnimacion("Reagendando Cita");
                $('#btnReagendar').attr('disabled', true);
                axios.post(principalUrl + "crear/citafisica", reagendaCita)
                    .then((respuesta) => {  
                        Swal.close();
                        $('#btnReagendar').attr('disabled', false);
                        if(respuesta.data.validacion == 1){
                            Swal.fire({
                                position: "top-end",
                                icon: "success",
                                title: "Cita Reagendada exitosamente!",
                                showConfirmButton: false,
                            });

                            location.href = principalUrl + "fisica/" + respuesta.data.id_citanueva;

                           // location.reload();
                        }else if(respuesta.data.validacion == 2){
                            Swal.fire({
                                title: '¡Lo siento, no es posible reprogramar la cita, la hora elegida ya está ocupada.!',
                                showDenyButton: false,
                                showCancelButton: false,
                                confirmButtonText: 'Ok, entendido',
                                denyButtonText: false,
                              }).then((result) => {
                                if (result.isConfirmed) {
                                   // location.reload();
                                } else if (result.isDenied) {
                                  Swal.fire('Changes are not saved', '', 'info')
                                }
                              })
                        }else if(respuesta.data.validacion == 55 ){
                            Swal.fire({
                                title: '<strong>Tu cita</strong>',
                                icon: 'error',
                                html:
                                  'Has alcanzado el límite máximo de citas físicas agendadas. Por favor, considere agendar una cita virtual.<br><strong> Si necesitas más ayuda llama al <a href="tel:+1631-609-9108">631-609-9108</a></strong>',
                                showCloseButton: true,
                                showCancelButton: false,
                                focusConfirm: false,
                                confirmButtonText:
                                  'Ok, entendido!',
                                confirmButtonAriaLabel: 'Thumbs up, great!',
                                cancelButtonText: false,
                                cancelButtonAriaLabel: 'Thumbs down'
                              })
                        }else if(respuesta.data.validacion === 35){
                            Swal.fire({
                                position: "top-center",
                                icon: "info",
                                title: "¡Ya existe una cita con tu número de teléfono para la fecha que deseas reagendar tu cita!",
                                showConfirmButton: false,
                            });
                        }
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
    }else{

    Swal.fire({
        title: "Reagendar cita",
        text: "¿Estas seguro de reagendar cita para la fecha: "+fechatext+", en la oficina: "+oficinaseleccionanda+" ?",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "SI",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            mostrarAnimacion("Reagendando Cita");
            $('#btnReagendar').attr('disabled', true);
            axios.post(principalUrl + "cita/reagendar", reagendaCita)
                .then((respuesta) => {
                    Swal.close();
                    $('#btnReagendar').attr('disabled', false);
                    if(respuesta.data == 1){
                        Swal.fire({
                            position: "top-end",
                            icon: "success",
                            title: "Cita Reagendada exitosamente!",
                            showConfirmButton: false,
                        });
                        location.reload();
                    }else if(respuesta.data == 2){
                        Swal.fire({
                            title: 'La cita no se pudo reagendar. Los cupos para esa hora ya están ocupados.',
                            showDenyButton: false,
                            showCancelButton: false,
                            confirmButtonText: 'Ok, entendido',
                            denyButtonText: false,
                          }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            } else if (result.isDenied) {
                              Swal.fire('Changes are not saved', '', 'info')
                            }
                          })
                    }else if(respuesta.data == 55 ){
                        Swal.fire({
                            title: '<strong>Tu cita</strong>',
                            icon: 'error',
                            html:
                              'Has alcanzado el límite máximo de citas físicas agendadas. Por favor, considere agendar una cita virtual.<br><strong> Si necesitas más ayuda llama al <a href="tel:+1631-609-9108">631-609-9108</a></strong>',
                            showCloseButton: true,
                            showCancelButton: false,
                            focusConfirm: false,
                            confirmButtonText:
                              'Ok, entendido!',
                            confirmButtonAriaLabel: 'Thumbs up, great!',
                            cancelButtonText: false,
                            cancelButtonAriaLabel: 'Thumbs down'
                          })
                    }else if(respuesta.data === 35){
                        Swal.fire({
                            position: "top-center",
                            icon: "info",
                            title: "¡Ya existe una cita con tu número de teléfono para la fecha que deseas reagendar tu cita!",
                            showConfirmButton: false,
                        });
                    }
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
 }

});


$(document).ready(function () {

    if( $('#estadocita').val() == "CANCELADA"){

        $(`#tarjetacita`).addClass(' border-danger');

        $(`.cita`).addClass('list-group-item-danger');


    }else if( $('#estadocita').val() == "CONFIRMADA" ){

        $(`#tarjetacita`).addClass(' border-success');

        $(`.cita`).addClass('list-group-item-success');

    }else if( $('#estadocita').val() == "PENDIENTE"){

        $(`#tarjetacita`).addClass(' border-warning');

        $(`.cita`).addClass('list-group-item-warning');
    }

    $("#modal_informativo").modal("show");

});



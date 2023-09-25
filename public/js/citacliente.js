var principalUrl = "http://localhost/mi_cita/public/";

//var principalUrl = "https://clientes.dailyappsetter.com/";


let formCancelar = document.getElementById("cancelarCita");
let formreagendar = document.getElementById("reagendarform");
let tipovista = $('#tipecita').val();

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

    
            axios.post(principalUrl + "cliente/confirmar",datosconfirm)
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

            $('#btnCancelar').attr('disabled', true);

           axios.post(principalUrl + "cliente/cancelar" , datoscancel )
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
                    if(element.nombre == ofici){     
                    $("#oficinas").append("<option selected value=" +element.id+">"+element.nombre+"</option>");
                    }else{
                    $("#oficinas").append("<option value=" +element.id+">"+element.nombre+"</option>");
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
        $("#reagendar").trigger("reset");
        $("#popup_reagendar").modal("show");
    });

    $('#fechascupos').on('change', function() {
        
        $(".hora").val("");
        var datosfechas = new FormData();
          var ofinaquetraer =$("#oficinas").val();
          var idcuposeleccionado = $("#fechascupos").val();
          var oficinaseleccionanda = $("#oficinas option:selected").text();

         if(ofinaquetraer === "oficina_virtual"){
            datosfechas.append("vista","virtual" );
         }else if(oficinaseleccionanda !== "Oficina Virtual"){
            datosfechas.append("vista","fisica" );
         }else{
        datosfechas.append("vista",tipovista );
         }
        datosfechas.append("idcupo",idcuposeleccionado);
        $("#horaReagendar").html("");            

    axios.post(principalUrl + "cita/listarHorario",datosfechas)
        .then((respuesta) => { 


            $("#nombreofic").val(respuesta.data.cantCitas.nombreoficina);
            $("#num_citas").val(respuesta.data.cantCitas.cant_citas);
            $("#fecharea").val( moment(respuesta.data.cantCitas.start).utc().locale('es').format("dddd DD [de] MMMM [del] YYYY"));
            $("#horaReagendar").html('');


            if(respuesta.data.hora[0].cant_citas != null){

            $("#horaReagendar").append("<option enabled selected value=''>Horas</option>");

                var horasvacias = 0;
                respuesta.data.hora.forEach(function (element) {
                    if(element.total00 < element.cant_citas || element.total30 < element.cant_citas ){
                        $("#horaReagendar").append("<option value=" +element.hora24.split(':')[0] +">"+element.hora12.split(':')[0]+"</option>");
                        horasvacias++;
                    }
                });

                if(horasvacias == 0){
                    $("#horaReagendar").append("<option readonly='true' value=''>No hay horas vacias</option>");
                }

            }else{

                $("#horaReagendar").append("<option enabled selected value=''>Horas</option>");

                var horasvacias = 0;
                respuesta.data.contadorHorascitas.forEach(function (element) {

                            if(element.total00 < element.cant_citas ){
                                horasvacias++;

                                if(element.hora12.split(':')[1] == '00'){
                                    $("#horaReagendar").append("<option value=" +element.hora24.split(':')[0] +">"+element.hora12.split(':')[0]+"</option>");
                                }else{
                                    var elementoExiste = respuesta.data.contadorHorascitas.some(function(item) {

                                        if( item.hora12 === element.hora12.split(':')[0]+':00'){
                                            if(item.total00  < item.cant_citas){
                                                return true;
                                            }else{
                                                return false;
                                            }
                                        }
                                    });

                                    if(elementoExiste == true){
                                    }else{
                                        $("#horaReagendar").append("<option value=" +element.hora24.split(':')[0] +">"+element.hora12.split(':')[0]+"</option>");
                                    }
                                }
                            }
                });
                if(horasvacias == 0){
                    $("#horaReagendar").append("<option readonly='true' value=''>No hay horas vacias</option>");
                }

            }

        })
        .catch((error) => {
            if (error.response) {
                console.log(error.response.data);
            }
        }); 
    });


    $('#oficinas').on('change', function() {
        var idofi= $("#oficinas").val();
        var idcita= $("#idcita").val();

        var datosoficinas = new FormData();

        var oficinaseleccionanda = $("#oficinas option:selected").text();

        if(oficinaseleccionanda !== "Oficina Virtual"){
            console.log("oficina seleccionanda "+oficinaseleccionanda);
            datosoficinas.append("vista","fisica" );
        }else{
        datosoficinas.append("vista",tipovista );
        }
        datosoficinas.append("oficinas",$("#oficinas").val() );
        datosoficinas.append("idcita",$("#idcita").val());

        $("#fechascupos").html("");
        //$("#oficinas").html("");
       // var ofici = $("#nombreoficina").val();
        console.log(idcita);
        axios.post(principalUrl + "cliente/fechasoficinas", datosoficinas )
        .then((respuesta) => {
            moment.locale("es");
            $("#fechascupos").append("<option selected readonly value=''>Fechas</option>");


                if(respuesta.data.length > 0 ){
                    respuesta.data.forEach(function (element) {   

                        var fecha_actual = moment().format('YYYY-MM-DD');
                        var fecha_cupo = moment.utc(element.start).format('YYYY-MM-DD');

                        if(fecha_cupo > fecha_actual){
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
    var cupo = $("#fechascupos").val();
    var hora = $("#horaReagendar").val();
    var oficina = $("#nombreofic").val();
    var fecha = $("#fecharea").val();

    if (cupo === "" || hora === "" ) {
        Swal.fire("¡Debe llenar todos los datos requeridos!");
        return;
    }

    var reagendaCita = new FormData(formreagendar);
    reagendaCita.append("vista",tipovista );
   // reagendaCita.append("cita_oficina",tipovista );
   var oficinaseleccionanda = $("#oficinas option:selected").text();

    if(tipovista === "fisica" && $("#oficinas").val() === "oficina_virtual" ){

        Swal.fire({
            title: "Reagendar cita virtual",
            text: "¿Estás seguro de que deseas cambiar tu cita física a una cita virtual para "+fecha+" ?" ,
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "SI",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                $('#btnReagendar').attr('disabled', true);
                axios.post(principalUrl + "crear/citafisica", reagendaCita)
                    .then((respuesta) => {  
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
            text: "¿Estás seguro de que deseas cambiar tu cita virtual a una cita física para "+fecha+" en la "+oficinaseleccionanda+" ?" ,
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "SI",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                $('#btnReagendar').attr('disabled', true);
                axios.post(principalUrl + "crear/citafisica", reagendaCita)
                    .then((respuesta) => {  
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
        text: "¿Estas seguro de reagendar cita para la fecha: "+fecha+", en la oficina: "+oficina+" ?",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "SI",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
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



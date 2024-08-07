@extends('layouts.app')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.19.2/locale/es.js"></script>


<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<script src="{{ asset('js/citacliente.js') }}" defer></script>

<!-- 
<script src="https://unpkg.com/imask"></script>
 -->

<style>
@media screen and (max-width:576px) {
    .btn-group {
        display: flex;
        flex-direction: column;
    }
}

.todo {
    display: flex;
    gap: 12px;
}

.todo input {
    width: 80%;
}

table {

    table-layout: fixed;
}

table td {
    word-wrap: break-word;
    max-width: 400px;
}

#registro_horas td {
    white-space: inherit;
}

table.display {
    table-layout: fixed;
}

.jumbotron {
    padding: 2rem 1rem;
    margin-bottom: 2rem;
    background-color: #e9ecef;
    border-radius: .3rem;
}

.cita {
    color: black;
}
</style>

<div class="container">
    
<input type="hidden" value="{{$vista}}" id="tipecita" name="tipecita"></input>

<input type="hidden" value="{{$cliente->idcita}}" id="idcita" name="idcita"></input>
<input type="hidden" value="{{$cliente->idcupo}}" id="idcupo" name="idcupo"></input>

<input type="hidden" value="{{$cliente->nombreestado}}" name="estadocita" id="estadocita" />

<input type="hidden" value="{{$cliente->nombrec}}" name="nombre" id="nombre" />
<input type="hidden" value="{{$cliente->apellidos}}" name="apellidos" id="apellidos" />
<input type="hidden" value="{{$cliente->nombreo}}" name="nombreoficina" id="nombreoficina" />
<input type="hidden" value="{{$cliente->start}}" name="fechacita" id="fechacita" />

<input type="hidden"
    value="{{ \Carbon\Carbon::parse($cliente->start)->locale('es')->isoformat('dddd D \d\e MMMM \d\e\l Y')}}"
    name="fechac" id="fechac" />


<div class="col-md-12" style="background-color: ">



    <div class="jumbotron">
        <h1 class="display" style="text-align: center;">¡Hola! {{$cliente->nombrec}} {{$cliente->apellidos}}</h1>


        <h1 style="text-align: center;">Gestiona tu cita</h1>
        <hr>
        <p class="" style="margin-bottom: 0 !important"><strong>
                <h4>Tu cita esta agendada es la siguiente:</h4>
            </strong> </p>
        <hr>
        <p class="lead">
        <h2>Oficina: {{$cliente->nombreo}}</h2>
        <h2>Fecha: {{ \Carbon\Carbon::parse($cliente->start)->locale('es')->isoformat('dddd D \d\e MMMM \d\e\l Y')}}
        </h2>
        </p>
    </div>

    <br>

    <div style="">
        <div class="card border " style="  border: 50px solid; border-style: solid;" id="tarjetacita">
            <div class="card-header" style=" text-align: center;"> <strong> MI CITA </strong></div>
            <div class="card-body text-dark">

                <li class="list-group-item cita ">
                    <h5 class="card-title">ESTADO DE LA CITA :&nbsp; {{$cliente->nombreestado}} </h5>
                </li>
                <br>
                <li class="list-group-item cita ">
                    <p class="" style="margin-bottom: 0 !important"> HORA DE TU CITA:&nbsp;
                        {{ \Carbon\Carbon::parse($cliente->hora)->format('h:i a') }}
                    </p>
                </li>
                <br>

                <li class="list-group-item cita ">
                    <p class="" style="margin-bottom: 0 !important">USTED FUE ATENDIDO POR:&nbsp; {{$cliente->name}}</p>
                </li>

            </div>
        </div>
    </div>




    <div class="col-md-12 border border-dark"
        style="display: flex; align-items: center; justify-content: center; text-align: center;">
        <div class="col-md-12">
            <div class="btn-group mr-3 " role="group" aria-label="button group">

                <div style="padding: 15px;text-align: center;" id="confirmar" class="border border-dark p-3">
                    <a data-target="#" data-toggle="modal" class="MainNavText" href="#"><img
                            src="{{ asset('iconos/confirmar.png') }}" class="btnexcel" /></a>
                    <p>CONFIRMAR CITA</p>
                </div>

                <div class="container" style="width: 200px;"></div>

                <div style="padding: 15px; text-align: center;" id="reagendar" class="border border-dark p-3">
                    <a data-target="#" data-toggle="modal" id="" class="MainNavText" href="#"><img
                            src="{{ asset('iconos/reagendar.png') }}" class="btnexcel" /></a>
                    <p for="reagendar">REAGENDAR CITA</p>
                </div>

                <div class="container" style="width: 200px;"></div>

                <div style="padding: 15px;text-align: center;" id="cancelar" class="border border-dark p-3">
                    <a ata-target="#" data-toggle="modal" class="MainNavText" href="#"><img
                            src="{{ asset('iconos/cerrar.png') }}" class="btnexcel" /></a>
                    <p>CANCELAR CITA</p>
                </div>
            </div>

        </div>
    </div>

    <br><br>

</div>

<br>


<div class="col-md-12  mb-4">
    <div class="card">
        <div class="card-body">
            <h5 class="cart-title"><strong>Recuerda que todas nuestras asesorías son completamente gratis</strong></h5>
            <br>
            <h5 class="cart-title"><strong>Documentos a presentar al día de la cita</strong></h5>
            <div class="row mb-3 mt-3">

                <div class="col-md-7 my-auto">
                    <p class="" style="margin-bottom: 0 !important"><strong>Los documentos requeridos para PERSONAS CON
                            SOCIAL:</strong> </p>

                    <p class="" style="margin-bottom: 0 !important">✅ Comprobantes de taxes del 2022</p>
                    <p class="" style="margin-bottom: 0 !important">✅ Comprobantes de taxes del 2023</p>
                    <p class="" style="margin-bottom: 0 !important">✅ Documento de identificación, puede ser la licencia o el pasaporte</p>
                    <p class="" style="margin-bottom: 0 !important">✅ Comprobantes de ingreso o colilla de pago</p>
                    <p class="" style="margin-bottom: 0 !important">✅ Copia de Social Security Number </p>
                    <p class="" style="margin-bottom: 0 !important">✅ El último estado de cuenta bancario donde se refleje el Down-payment</p>

                    <br>
                    <p class="" style="margin-bottom: 0 !important"><strong>Los documentos requeridos para PERSONAS CON TAX ID:</strong> </p>

                    <p class="" style="margin-bottom: 0 !important">✅ COPIA DE SU TAX ID</p>
                    <p class="" style="margin-bottom: 0 !important">✅ Documento de identificación, puede ser la licencia o el pasaporte</p>
                    <p class="" style="margin-bottom: 0 !important">✅ El último estado de cuenta bancario donde se refleje el Down-payment</p>
                    <p class="" style="margin-bottom: 0 !important">✅ Pasaporte (6 meses de vigencia minina)</p>

                    <br>

                    <p class="" style="margin-bottom: 0 !important"><strong>¡Estos documentos son por cada persona interesada en comprar la casa!</strong> </p>

                    <br>
                    <p class="" style="margin-bottom: 0 !important"><strong> Si necesitas más ayuda llama al <a  href="tel:+1631-609-9108">631-609-9108</a></strong></p>

                </div>
            </div>

        </div>
    </div>
</div>
</div>


</div>



<!-- Modal cancelar-->
<div class="modal fade" id="popup_cancelar" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    ¿Por qué quiere cancelar su cita?
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" id="cancelarCita">

                    {!! csrf_field() !!}
                    <div class="form-group ">
                        <label for="start">Motivo:</label>
                        <textarea name="motivo" rows="5" required="" id="motivo" class="form-control" cols="50"
                            autocomplete="off"></textarea>
                    </div>
                    <input type="hidden" name="cita_id" value="" />
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btnCancelar">Guardar</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal reagendar-->
<div class="modal fade" id="popup_reagendar" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Reagendar mi cita
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" id="reagendarform">
                    {!! csrf_field() !!}
                    <div class="form-group ">
                        <label for="start">Oficinas :</label>
                        <select name="oficinas" id="oficinas" class="form-control">
                        </select>
                    </div>

                    <div class="form-group ">
                        <label for="start">Fechas:</label>
                        <select name="fechascupos" id="fechascupos" onchange="" class="form-control">
                        </select>
                    </div>

                    <div class="form-group ">
                        <label for="start">Horario:</label>
                        <select name="horas_cupo" id="horas_cupo" class="form-control hora" >
                        </select>
                    </div>


                    <div class="form-group" style="display: none">
                        <label for="hora">Hora Cita </label>
                        <div style="display: flex;justify-content: space-around;">
                            <select name="horaReagendar" onchange="" id="horaReagendar"
                                class="form-control col-md-3 hora">
                            </select>

                            <select name="minutosReagendar" id="minutosReagendar" class="form-control col-md-3 hora">
                                <option value="00" selected>00</option>
                                <option value="30">30</option>
                            </select>
                            <input type="text" class="form-control col-md-3 hora" required="" name="horarioreagenda"
                                id="horarioreagenda" readonly="readonly" value="">
                        </div>
                    </div>

                    <input type="hidden" value="" id="horareagenda" name="horareagenda"></input>
                    <input type="hidden" value="" id="num_citas" name="num_citas"></input>
                    <input type="hidden" id="cita_id" name="cita_id" value="{{$cliente->idcita}}" />

                    {{-- <input type="hidden" id="fecharea" name="fecharea" value="" />
                    <input type="hidden" id="nombreofic" name="nombreofic" value="" /> --}}


                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btnReagendar">Reagendar</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal informativo-->
<div class="modal fade" id="modal_informativo" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Administra tu cita
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" id="modal_informativo">

                    {!! csrf_field() !!}
                    <div class="col-md-12 my-auto">
                        <p class="" style="margin-bottom: 0 !important"><strong>Bienvenido a tu cita de Contigo Mortgage
                            </strong> </p>

                        <br>
                        <p class="" style="margin-bottom: 0 !important">✅ <strong>Confirmar cita:</strong> es donde tu
                            confirmas que asistiras a las cita que agendaste.</p>
                        <br>
                        <p class="" style="margin-bottom: 0 !important">📅 <strong>Reagendar cita:</strong> puedes
                            agendar tu cita en otro dia </p>
                        <br>
                        <p class="" style="margin-bottom: 0 !important">❌ <strong>Cancela cita:</strong> podras cancelar
                            tu cita, ingresar un motivo por el cual cancelas tu cita, recuerda que tienes la opcion de
                            reagendar por si algun motivo no puedes presentarte en ese dia </p>

                        <br>
                        <p class="" style="margin-bottom: 0 !important"><strong> Si necesitas más ayuda llama al <a
                                    href="tel:+1631-609-9108">631-609-9108</a></strong></p>

                    </div>
                    <input type="hidden" name="cita_id" value="" />
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>



@endsection
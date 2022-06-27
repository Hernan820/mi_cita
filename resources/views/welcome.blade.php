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
</style>


<input type="hidden" value="{{$cliente->idcita}}" id="idcita" name="idcita"></input>
<input type="hidden" value="{{$cliente->idcupo}}" id="idcupo" name="idcupo"></input>


<input type="hidden" value="{{$cliente->nombrec}}" name="nombre" id="nombre" />

<input type="hidden" value="{{$cliente->apellidos}}" name="apellidos" id="apellidos" />

<input type="hidden" value="{{$cliente->nombreo}}" name="nombreoficina" id="nombreoficina" />

<input type="hidden" value="{{ \Carbon\Carbon::parse($cliente->start)->locale('es')->isoformat('dddd D \d\e MMMM \d\e\l Y')}}" name="fechac" id="fechac" />


<div class="col-md-12" style="background-color: ">



    <div class="jumbotron">
        <h1 class="display-4" style="text-align: center;">Hola! {{$cliente->nombrec}} {{$cliente->apellidos}}</h1>


<h1 style="text-align: center;" >Gestiona tu cita</h1>
<hr>
<p class="" style ="margin-bottom: 0 !important"><strong><h4>Tu cita esta agendada en la siguiente oficina y fecha:</h4></strong> </p>
<hr>
        <p class="lead">
        <h2 > Oficina: {{$cliente->nombreo}} &nbsp; &nbsp;Fecha:
                &nbsp;{{ \Carbon\Carbon::parse($cliente->start)->locale('es')->isoformat('dddd D \d\e MMMM \d\e\l Y')}}
        </h2>
        </p>
    </div>

    <idv class="col-md-12 table-responsive">
        <table id="registro_horas" class="table table-striped table-bordered dt-responsive nowrap datatable"
            class="display" cellspacing="0" cellpadding="3" width="100%" style="background-color: ">
            <thead>
                <tr>
                    <th class="">Hora</th>
                    <th class="">Cliente</th>
                    <th class="">Telefono</th>
                    <th class="">Operador</th>
                    <th class="">Estado</th>
                </tr>
            </thead>
            <tbody id="citacliente">
                <tr>
                    <td>{{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $cliente->hora)->locale('es')->format('h:i a')}}</td>
                    <td>{{$cliente->nombrec}}&nbsp;{{$cliente->apellidos}}</td>
                    <td>{{$cliente->telefono}}</td>
                    <td>{{$cliente->name}}</td>
                    <td style=" " >{{$cliente->nombreestado}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<br>


<div class="container"  style="display: flex; align-items: center;  justify-content: center; ">
<div class="col-md-12">
      <div class="btn-group mr-3 " role="group" aria-label="button group">

        <div  style="padding: 15px;">
        <button type="button" id="reagendar" style="width: 300px;" class="btn btn-primary">Reagendar Cita</button>
        </div>

        <div style="padding: 15px;">
        <button type="button" id="confirmar" style="width: 300px;"  class="btn btn-success">Confirmar Cita</button>
        </div>

        <div style="padding: 15px;">
        <button type="button" id="cancelar" style="width: 300px;"  class="btn btn-danger">Cancelar Cita</button>
        </div>


      </div>
    </div>
</div>


<div class="col-md-12  mb-4">
        <div class="card">
            <div class="card-body">
            <h5 class="cart-title"><strong>Recuerda que todas nuestras asesorias son completamente gartis</strong></h5>
<br>
                <h5 class="cart-title"><strong>Documentos a presentar al dia de la cita</strong></h5>
                <div class="row mb-3 mt-3">
        
                    <div class="col-md-7 my-auto" >
                    <p class="" style ="margin-bottom: 0 !important"><strong>Los documentos requeridos para personas con social:</strong> </p>

                        <p class="" style ="margin-bottom: 0 !important">✅ Comprobantes de taxes del 2020</p>
                        <p class="" style ="margin-bottom: 0 !important">✅ Comprobantes de taxes del 2021</p>
                        <p class=""  style ="margin-bottom: 0 !important">✅ Documento de identificación, puede ser la licencia o el pasaporte</p>
                        <p class="" style ="margin-bottom: 0 !important">✅ Comprobantes de ingreso o colilla de pago</p>
                        <p class="" style ="margin-bottom: 0 !important">✅ Copia de Social Security Number </p>
                        <p class="" style ="margin-bottom: 0 !important">✅ El último estado de cuenta bancario donde se refleje el Down-payment</p>

                        <br>
                        <p class="" style ="margin-bottom: 0 !important"><strong>Los documentos requeridos para PERSONAS CON TAX ID:</strong> </p>

                        <p class="" style ="margin-bottom: 0 !important">✅ COPIA DE SU TAX ID</p>
                        <p class="" style ="margin-bottom: 0 !important">✅ Documento de identificación, puede ser la licencia o el pasaporte</p>
                        <p class=""  style ="margin-bottom: 0 !important">✅ Comprobantes de ingreso o colilla de pago</p>
                        <p class="" style ="margin-bottom: 0 !important">✅ El último estado de cuenta bancario donde se refleje el Down-payment</p>

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
                ¿ Por que quiere cancelar su cita ?
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
                        <label for="start">Fechas y oficina :</label>
                        <select name="cuposid" id="cuposid" onchange="" class="form-control">
                            
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="hora">Hora Cita </label>
                        <div style="display: flex;justify-content: space-around;">
                            <select name="horaReagendar" onchange="" id="horaReagendar"
                                class="form-control col-md-3 hora">
                            </select>

                            <select name="minutosReagendar" id="minutosReagendar"
                                class="form-control col-md-3 hora">
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

                    <input type="hidden" id="fecharea" name="fecharea" value="" />
                    <input type="hidden" id="nombreofic" name="nombreofic" value="" />


                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btnReagendar">Reagendar</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection
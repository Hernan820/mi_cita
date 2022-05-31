@extends('layouts.app')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

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


<input type="hidden" value="" id="id_cupo" name="id_cupo"></input>
<input type="hidden" name="usuario_log" id="usuario_log" value="" />

<div class="col-md-12" style="background-color: ">



    <div class="jumbotron">
        <h1 class="display-4">Hola! {{$cliente->nombrec}} {{$cliente->apellidos}}</h1>
</br>

<h1>Gestiona tu cita</h1>
<hr>
        <p class="lead">
        <h2> Oficina: {{$cliente->nombreo}} &nbsp; &nbsp; &nbsp;  Fecha:
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
                    <th class="">Descripcion</th>
                    <th class="">Operador</th>
                    <th class="">Estado</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $cliente->hora)->format('H:i a')}}</td>
                    <td>{{$cliente->nombrec}}&nbsp;{{$cliente->apellidos}}</td>
                    <td>{{$cliente->telefono}}</td>
                    <td>{{$cliente->descripcion}}</td>
                    <td>{{$cliente->name}}</td>
                    <td>{{$cliente->nombreestado}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<br><br><br><br>

<div class="container">

<button type="button" class="btn btn-primary">Reagendar Cita</button>
<button type="button" class="btn btn-success">Confirmar Cita</button>
<button type="button" class="btn btn-danger">Cancelar Cita</button>
</div>



    <div class="col-md-12">
      <div class="btn-group mr-2 " role="group" aria-label="button group">

      <button type="button" class="btn btn-primary">Reagendar Cita</button>
<button type="button" class="btn btn-success">Confirmar Cita</button>
<button type="button" class="btn btn-danger">Cancelar Cita</button>
      </div>
    </div>
 

@endsection
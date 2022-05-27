@extends('layouts.app')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>


<!-- 
<script src="https://unpkg.com/imask"></script>
 -->

<style>
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
</style>




<input type="hidden" value="" id="id_cupo" name="id_cupo"></input>
<input type="hidden" name="usuario_log" id="usuario_log" value="" />

<div class="col-md-12" style="background-color: ">
    <div class="jumbotron col-md-12 col d-flex justify-content-between ">
    <h2><strong> Oficina: {{$cliente->nombrec}} &nbsp; &nbsp; &nbsp; Fecha:
                &nbsp;{{ \Carbon\Carbon::parse($cliente->start)->locale('es')->isoformat('dddd D \d\e MMMM \d\e\l Y')}}</strong> </h2>
    </div>



    <idv class="col-md-12 table-responsive">
        <table id="registro_horas" class="table table-striped table-bordered dt-responsive nowrap datatable"
            class="display" cellspacing="0" cellpadding="3" width="100%" style="background-color: ">
            <thead>
                <tr>
                       <th class="col-md-1">Hora</th>
                        <th class="col-md-2">Cliente</th>
                        <th class="col-md-1">Telefono</th>
                        <th class="col-md-2 text-left ">Descripcion</th>
                        <th class="col-md-2">Operador</th>
                        <th class="col-md-1">Estado</th>
                        <th class="col-md-2">Comentario</th>
                        <th class="col-md-1">Asis.</th>
                        <th class="col-md-1"></th>
                </tr>
            </thead>
            <tbody>


 
                <tr>
                <td>{{$cliente->hora}}</td>

                <td>{{$cliente->nombrec}}&nbsp;{{$cliente->apellidos}}</td>
                <td>{{$cliente->telefono}}</td>
                <td>{{$cliente->descripcion}}</td>
                <td>{{$cliente->name}}</td>
                <td>{{$cliente->id_estado}}</td>

                </tr>


                


            </tbody>
        </table>
</div>
</div>

@endsection

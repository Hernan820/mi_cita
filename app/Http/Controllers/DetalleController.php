<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cupo;
use App\Models\User;
use App\Models\Oficina;
use App\Models\Cliente;
use App\Models\DetalleCupo;


class DetalleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($idc)
    {

        $cliente = DetalleCupo::join("clientes","clientes.id", "=", "detalle_cupos.id_cliente")
        ->join("users","users.id", "=", "detalle_cupos.id_usuario")
        ->join("cupos","cupos.id","=","detalle_cupos.id_cupo")
        ->join("oficinas","oficinas.id","=","cupos.id_oficina")
        
        ->select("clientes.*","clientes.nombre as nombrec","detalle_cupos.*","cupos.*","users.*","oficinas.*")
        
        ->where("detalle_cupos.id_cliente", "=", $idc)
        ->where("detalle_cupos.estado_cupo", "=",null)
        ->where("detalle_cupos.id_estado", "!=",3)
        
        ->orderBy("detalle_cupos.hora", 'asc')
        ->first();


//        return response()->json($cliente);

       return view('welcome',compact('cliente'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

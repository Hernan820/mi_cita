<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Models\Cupo;
use App\Models\User;
use App\Models\Oficina;
use App\Models\Cliente;
use App\Models\DetalleCupo;
use App\Models\CuposHorario;


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
        ->join("estados","estados.id","=","detalle_cupos.id_estado")

        ->select("clientes.*","clientes.nombre as nombrec","estados.nombre as nombreestado","detalle_cupos.*","detalle_cupos.id as idcita","cupos.*","cupos.id as idcupo","users.*","oficinas.*","oficinas.nombre as nombreo")
        
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
    public function create($idcita)
    {
        $cita = DetalleCupo::find($idcita);
        $cita->id_estado = 1;
        $cita->save();  

        return 1 ;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function cancelar($idcita, Request $request)
    {
        $cita = DetalleCupo::find($idcita);
        $cita->motivo_cancelacion = $request->motivo ;
        $cita->id_estado = 2 ;
        $cita->save();   

        return 1 ;
     }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cupos()
    {

        $fechaActual = date('Y-m-d');

        $cupos = Cupo::join("oficinas","oficinas.id", "=", "cupos.id_oficina")
        ->select("oficinas.nombre", "cupos.start","cupos.id")
        ->where("cupos.start", ">", $fechaActual)
        ->where("cupos.estado_cupo", "=", null)
        ->orderBy('cupos.start', 'asc')
        ->get();  

        return response()->json($cupos);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function listarhoras($id)
    {

       /* $hora =CuposHorario::join("horarios","horarios.id", "=", "cupos_horarios.id_horario")
        ->select("cupos_horarios.id_cupo","horarios.hora12","horarios.hora24")
        ->where("cupos_horarios.id_cupo","=",$id)
        ->get();*/

        $sql = "SELECT h.hora24, h.hora12, 
        (select COUNT(*) from detalle_cupos dc 
         WHERE dc.id_cupo = $id and HOUR(dc.hora) = h.hora24 and MINUTE(dc.hora) = 0 and dc.id_estado != 3 AND dc.id_estado !=2 and dc.estado_cupo IS null ) as total00,
        (select COUNT(*) from detalle_cupos dc
        WHERE dc.id_cupo = $id and HOUR(dc.hora) = h.hora24 and MINUTE(dc.hora) = 30 and dc.id_estado != 3 AND dc.id_estado !=2 and dc.estado_cupo IS null ) as total30,
        cupos.cant_citas
        FROM cupos_horarios ch
        LEFT JOIN horarios h on ch.id_horario = h.id
        INNER JOIN cupos on cupos.id = ch.id_cupo
        WHERE ch.id_cupo = $id";
        
        $hora = DB::select($sql);

        $cantCitas= Cupo::join("oficinas","oficinas.id","=","cupos.id_oficina")
                          ->select("cupos.*","oficinas.nombre as nombreoficina") 
                          ->where("cupos.id","=", $id)
                          ->first();
        
        
        

        return response()->json(['hora' => $hora, 'cantCitas' => $cantCitas],200);
    
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reagendar(Request $request)
    {

     //concatena hora
     $horareagenda= $request->horaReagendar.':'.$request->minutosReagendar;


        $contadorCitas = DetalleCupo::join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
        -> where("detalle_cupos.id_cupo","=",$request->cuposid)
        ->where("detalle_cupos.hora","=",$horareagenda)
        ->where(function ($query) {
             $query->where("detalle_cupos.id_estado","!=",3)
                   ->where("detalle_cupos.id_estado","!=",2)
                   ->where("detalle_cupos.estado_cupo","=",null);
         })
         ->count();

         if($contadorCitas < $request->num_citas){

            $cupo = Cupo::join("oficinas","oficinas.id", "=", "cupos.id_oficina")
            ->select("oficinas.nombre as title", "cupos.start","cupos.id")
            ->where("cupos.id", "=", $request->cuposid)
            ->get()
            ->first();
    
           //reagenda la cita
            $cita = DetalleCupo::find($request->cita_id);
    
            //crea una cita con las mismas propiedades
            $detallecupo= new DetalleCupo;
            $detallecupo->id_cupo = $request->cuposid;
            $detallecupo->id_cliente =$cita->id_cliente;
            $detallecupo->id_estado = 4;
            $detallecupo->id_usuario = $cita->id_usuario;
            $detallecupo->hora = $horareagenda;
            $detallecupo->descripcion = $cita-> descripcion;
            $detallecupo-> save(); 
    
            $cita->id_estado = 3;
            $cita->descripcion ="La cita ha sido reagendada para la oficina ".$cupo->title." para la fecha ". date('d-m-Y', strtotime($cupo->start));
            $cita->save();    
        
        return 1 ;
         }else{

            return 2;
         }
        
        
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

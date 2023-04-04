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
use Carbon\Carbon;

use Twilio\Rest\Client;

define('WB_TOKEN', '963fe4d6878286fc02a3b4571b84162f6176c9f6c3fc4');
define('WB_FROM', '16315067068');
//date_default_timezone_set("America/New_York");


class DetalleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

  
     
   public function encriptacion($valor){
        $encrypted_data = base64_decode($valor);
        return openssl_decrypt($valor, 'aes-256-cbc', '1234567812345678', false, '1234567812345678');
    }
 

    public function index($vista,$idc)
    {
       // $idcliente = $this->encriptacion($idc);

       $idcliente =base64_decode($idc);

       if($vista == 'fisica'){

        $cliente = DetalleCupo::join("clientes","clientes.id", "=", "detalle_cupos.id_cliente")
        ->join("users","users.id", "=", "detalle_cupos.id_usuario")
        ->join("cupos","cupos.id","=","detalle_cupos.id_cupo")
        ->join("oficinas","oficinas.id","=","cupos.id_oficina")
        ->join("estados","estados.id","=","detalle_cupos.id_estado")
        ->select(DB::raw(''))
        ->where("detalle_cupos.id_cliente", "=", $idcliente)
        ->where("detalle_cupos.estado_cupo", "=",null)
        ->where("detalle_cupos.id_estado", "!=",3)
        ->orderBy("detalle_cupos.hora", 'asc')
        ->first();

       }else if($vista == 'virtual'){


       $cliente = DB::connection('mysql2')->table('detalle_cupos')
        ->selectRaw("(CASE WHEN estados.nombre = 'pendiente' THEN 'PENDIENTE' ELSE (CASE WHEN estados.nombre = 'confirmado' THEN 'CONFIRMADA' ELSE (CASE WHEN estados.nombre = 'cancelado' THEN 'CANCELADA' ELSE (CASE WHEN estados.nombre = 'no answer' THEN 'NO ANSWER' END) END)  END) END) AS nombreestado ,clientes.*,clientes.nombre as nombrec,detalle_cupos.*,detalle_cupos.id as idcita,cupos.*,cupos.id as idcupo,users.*,oficinas.*,oficinas.nombre as nombreo")
        ->join('clientes', 'clientes.id', '=', 'detalle_cupos.id_cliente')
        ->join('users', 'users.id', '=', 'detalle_cupos.id_usuario')
        ->join('cupos', 'cupos.id', '=', 'detalle_cupos.id_cupo')
        ->join('oficinas', 'oficinas.id', '=', 'cupos.id_oficina')
        ->join('estados', 'estados.id', '=', 'detalle_cupos.id_estado')
        ->where('detalle_cupos.id_cliente', $idcliente)
        ->whereNull('detalle_cupos.estado_cupo')
        ->where('detalle_cupos.id_estado', '<>', 3)
        ->orderBy('detalle_cupos.hora', 'ASC')
        ->first();


       }else{
        return view('errors.404');

       }

        if($cliente == null){
            return view('errors.404');
        }else{
            return view('welcome',compact('cliente'));
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function link_send($to,$url,$tipo)
    {

        $custom_uid = "unique-" . time();
        $to = filter_var($to, FILTER_SANITIZE_NUMBER_INT);
        $msg = urlencode($url);
      
        
      
        switch ($tipo) {
            case 1:
                // Link with preview
                $data = "https://www.waboxapp.com/api/send/link?token=" . WB_TOKEN . "&uid=" . WB_FROM ."&custom_uid=" . $custom_uid . "&to=" . $to . "&url=" . $url ;
                break;
      
            case 2:
             //Send Media Media
                $description = '¡Todo-lo-que-necesitas-saber!';
            $caption =  '¿Como-tener-tu-casa-propia?';
                $url_thumb = "https://casademisuenos-usa.com/sms/team_acevedo.png";
                $data = "https://www.waboxapp.com/api/send/media?token=" . WB_TOKEN . "&uid=" . WB_FROM ."&custom_uid=" . $custom_uid . "&to=" . $to. "&caption=" .$caption. "&description=" .$description. "&url_thumb=" .$url_thumb. "&url=" .$url ;
                break;
      
            case 3:
                // Send message
                $data = "https://www.waboxapp.com/api/send/chat?token=" . WB_TOKEN . "&uid=" . WB_FROM . "&custom_uid=" . $custom_uid . "&to=" . $to . "&text=" . $msg;
            break;
      
            default:
                return false;
                break;
        }
      
        $curl = curl_init();
      
        curl_setopt($curl, CURLOPT_URL, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      
        $result = curl_exec($curl);
        curl_close($curl);
        $resp = json_decode($result);
      
        if ($resp == true) {
              return $resp;
          }else{
      
              for ($i=1; $i < 3 ; $i++) { 
      
                  $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $data);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($curl);
                $resp = json_decode($result);
      
                if ($resp->success == 1) {
                         $respuesta = $resp;
                         break;
                }else{
                    $respuesta = false;
                }
                  
              }
              return $respuesta;
          }
      
          return false;
    }


    /**
     * separacion.
     *
     * 
     */


    public function confirmar($idcita)
    {
        $cita = DetalleCupo::find($idcita);
        $cita->id_estado = 1;
        $cita->save();  

        $cliente = DetalleCupo::join("clientes","clientes.id", "=", "detalle_cupos.id_cliente")
        ->join("users","users.id", "=", "detalle_cupos.id_usuario")
        ->join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
        ->join("oficinas","oficinas.id", "=", "cupos.id_oficina")
        ->select("users.name","cupos.start","clientes.telefono","oficinas.direccion","detalle_cupos.hora")
        ->where('detalle_cupos.id','=',$idcita)
        ->first();

        $fechatexto= Carbon::parse($cliente->start)->locale('es')->isoformat('dddd D \d\e MMMM \d\e\l Y');

        $hora = Carbon::parse($cliente->hora)->format('h:i A');

        $horamedia = explode(" ", $hora);
        if($horamedia[1] == "PM"){
           $horatexto= $horamedia[0]." de la tarde"; 
        }else if($horamedia[1] == "AM"){
            $horatexto= $horamedia[0]." de la mañana"; 
        }

        $msg="!Hola! le saluda $cliente->name de parte de *Contigo Mortgage* 🏠✅
        
Su cita ha sido confirmada para el día $fechatexto a las $horatexto

La dirección de nuestra oficina es 
📍 $cliente->direccion

Los documentos requeridos para personas con social:

✅ Comprobantes de taxes del 2020
✅ Comprobantes de taxes del 2021
✅ Documento de identificación, puede ser la licencia o el pasaporte
✅ Comprobantes de ingreso o colilla de pago
✅ Copia de Social Security Number 
✅ El último estado de cuenta bancario donde se refleje el Down-payment

Los documentos requeridos para PERSONAS CON TAX ID:

✅ COPIA DE SU TAX ID
✅ Documento de identificación, puede ser la licencia o el pasaporte
✅ Comprobantes de ingreso o colilla de pago
✅ El último estado de cuenta bancario donde se refleje el Down-payment

!Estos documentos son por cada persona interesada en comprar la casa!
";
/************************************************************************************** */
$msgtxt="!Hola! le saluda $cliente->name de parte de *Contigo Mortgage* 
        
Su cita ha sido confirmada para el día $fechatexto a las $horatexto

La dirección de nuestra oficina es 
 $cliente->direccion

Los documentos requeridos para personas con social:

 Comprobantes de taxes del 2020
 Comprobantes de taxes del 2021
 Documento de identificación, puede ser la licencia o el pasaporte
 Comprobantes de ingreso o colilla de pago
 Copia de Social Security Number 
 El último estado de cuenta bancario donde se refleje el Down-payment

Los documentos requeridos para PERSONAS CON TAX ID:

 COPIA DE SU TAX ID
 Documento de identificación, puede ser la licencia o el pasaporte
 Comprobantes de ingreso o colilla de pago
 El último estado de cuenta bancario donde se refleje el Down-payment

!Estos documentos son por cada persona interesada en comprar la casa!";

        $array =str_split($cliente->telefono);
        $numeroCompleto="+1".$array[1].$array[2].$array[3].$array[6].$array[7].$array[8].$array[10].$array[11].$array[12].$array[13];

        $r = $this->link_send(+50379776604,$msg,$tipo=4); 

        $sid = "AC9e1475e1b32fec62e6dd712768584a72";
        $token  = "58ea12aa01f49e1965736ea94d043b24";
        $from= "+18334941535";
        $twilio = new Client($sid, $token);
            
      //  $twilio->messages->create( +6318943177, ['from' => $from,'body' => $msgtxt,] );
      $idcliente = $this->encriptacion("RUHaptvoSjpyoQX1/G8gww==");
      
      return (response()->json($idcliente));
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

        $usuario = DetalleCupo::join("users","users.id", "=", "detalle_cupos.id_usuario")
        ->join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
        ->join("oficinas","oficinas.id", "=", "cupos.id_oficina")
        ->join("clientes","clientes.id", "=", "detalle_cupos.id_cliente")
        ->select("clientes.telefono")
        ->where("detalle_cupos.id","=",$idcita)
        ->get()
        ->first();


        $msg="¡Hola! recuerda que puedes reagendar tu cita, contactándonos al 631-609-9108
Si tiene alguna duda estoy a la orden✅
                                        
Conócenos:        
                    
https://www.youtube.com/watch?v=UilV0wxXLaY&t=22s

";
/*************************************************************************** */
$msgtxt="¡Hola! recuerda que puedes reagendar tu cita, contactándonos al 631-609-9108
Si tiene alguna duda estoy a la orden

Puedes comunicarte a través de este WhatsApp https://wa.me/message/F4D3UQUHQTFAO1
 te esperamos. 
                                       

                    
https://www.youtube.com/watch?v=UilV0wxXLaY&t=22s
";


         $array =str_split($usuario->telefono);
         $numeroCompleto="+1".$array[1].$array[2].$array[3].$array[6].$array[7].$array[8].$array[10].$array[11].$array[12].$array[13];
        
          $r = $this->link_send(+50379776604,$msg,$tipo=4);  

          $sid = "AC9e1475e1b32fec62e6dd712768584a72";
          $token  = "58ea12aa01f49e1965736ea94d043b24";
          $from= "+18334941535";
          $twilio = new Client($sid, $token);
             
        //  $twilio->messages->create( +6318943177, ['from' => $from,'body' => $msgtxt,] );

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
            ->where("cupos.id", "=", $request->fechascupos)
            ->get()
            ->first();
    
           //reagenda la cita
            $cita = DetalleCupo::find($request->cita_id);
    
            //crea una cita con las mismas propiedades
            $detallecupo= new DetalleCupo;
            $detallecupo->id_cupo = $request->fechascupos;
            $detallecupo->id_cliente =$cita->id_cliente;
            $detallecupo->id_estado = 4;
            $detallecupo->id_usuario = $cita->id_usuario;
            $detallecupo->hora = $horareagenda;
            $detallecupo->descripcion = $cita-> descripcion;
            $detallecupo-> save(); 
    
            $cita->id_estado = 3;
            $cita->descripcion ="La cita ha sido reagendada para la oficina ".$cupo->title." para la fecha ". date('d-m-Y', strtotime($cupo->start));
            $cita->save(); 
            
            
            $usuario = DetalleCupo::join("users","users.id", "=", "detalle_cupos.id_usuario")
            ->join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
            ->join("oficinas","oficinas.id", "=", "cupos.id_oficina")
            ->join("clientes","clientes.id", "=", "detalle_cupos.id_cliente") 
            ->select("users.name","cupos.start","clientes.telefono","oficinas.direccion")
            ->where("detalle_cupos.id_cupo","=",$request->fechascupos)
            ->get()
            ->first();

                $fechatexto= Carbon::parse($usuario->start)->locale('es')->isoformat('dddd D \d\e MMMM \d\e\l Y');

                $hora = Carbon::parse($horareagenda)->format('h:i A');

                $horamedia = explode(" ", $hora);
                if($horamedia[1] == "PM"){
                   $horatexto= $horamedia[0]." de la tarde"; 
                }else if($horamedia[1] == "AM"){
                    $horatexto= $horamedia[0]." de la mañana"; 
                }

            $msg="Hola! le saluda $usuario->name de parte de *Contigo Mortgage* 🏠✅

Su cita ha sido reagendada para el día $fechatexto a las $horatexto
            
La dirección de nuestra oficina es 
📍 $usuario->direccion
            
Los documentos requeridos para personas con social:

✅ Comprobantes de taxes del 2020
✅ Comprobantes de taxes del 2021
✅ Documento de identificación, puede ser la licencia o el pasaporte
✅ Comprobantes de ingreso o colilla de pago
✅ Copia de Social Security Number 
✅ El último estado de cuenta bancario donde se refleje el Down-payment

Los documentos requeridos para PERSONAS CON TAX ID:

✅ COPIA DE SU TAX ID
✅ Documento de identificación, puede ser la licencia o el pasaporte
✅ Comprobantes de ingreso o colilla de pago
✅ El último estado de cuenta bancario donde se refleje el Down-payment

Estos documentos son por cada persona interesada en comprar la casa!
            
*Por favor ayúdanos a confirmar tu asistencia a través  de este WhatsApp y atenderte de la mejor manera. Será un gusto tenerte en nuestra oficina, te esperamos.*
            
Cualquier consulta puedes llamarnos al 631-609-9108
            
Si tiene alguna duda estoy a la orden✅
            
            
Conócenos:
            
https://www.youtube.com/watch?v=UilV0wxXLaY&t=22s";
/****************************************************************************************************** */
$msgtxt="¡Hola! le saluda $usuario->name de parte de *Contigo Mortgage*

Su cita ha sido reagendada para el día $fechatexto a las $horatexto
            
La dirección de nuestra oficina es 
 $usuario->
            
Los documentos requeridos para personas con social:

 Comprobantes de taxes del 2020
 Comprobantes de taxes del 2021
 Documento de identificación, puede ser la licencia o el pasaporte
 Comprobantes de ingreso o colilla de pago
 Copia de Social Security Number 
 El último estado de cuenta bancario donde se refleje el Down-payment

Los documentos requeridos para PERSONAS CON TAX ID:

 COPIA DE SU TAX ID
 Documento de identificación, puede ser la licencia o el pasaporte
 Comprobantes de ingreso o colilla de pago
 El último estado de cuenta bancario donde se refleje el Down-payment

!Estos documentos son por cada persona interesada en comprar la casa!
               
Por favor ayúdanos a confirmar tu asistencia a través de este WhatsApp https://wa.me/message/F4D3UQUHQTFAO1
y atenderte de la mejor manera. Será un gusto tenerte en nuestra oficina, te esperamos. 
            
Cualquier consulta puedes llamarnos al 631-609-9108
            
Si tiene alguna duda estoy a la orden
              
Conócenos:
            
https://www.youtube.com/watch?v=UilV0wxXLaY&t=22s";


            $array =str_split($usuario->telefono);
            $numeroCompleto="+1".$array[1].$array[2].$array[3].$array[6].$array[7].$array[8].$array[10].$array[11].$array[12].$array[13];
           
             $r = $this->link_send(+50379776604,$msg,$tipo=4);

             $sid = "AC9e1475e1b32fec62e6dd712768584a72";
             $token  = "58ea12aa01f49e1965736ea94d043b24";
             $from= "+18334941535";
             $twilio = new Client($sid, $token);
             
            // $twilio->messages->create( +6318943177, ['from' => $from,'body' => $msgtxt,] );
        
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
    public function oficinas($idcita)
    {

        $fechaActual = date('Y-m-d');

        $cita= DetalleCupo::join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
        ->join("oficinas","oficinas.id", "=", "cupos.id_oficina")
        ->select('cupos.start','oficinas.id')
        ->where('detalle_cupos.id','=',$idcita)
        ->first();

        $ofi= Oficina::join("cupos","cupos.id_oficina", "=", "oficinas.id")
        ->select('oficinas.id','oficinas.nombre')
        ->where('cupos.start','>=', $fechaActual)
        //->where('cupos.start','>=', $cita->start)
        ->groupBy('oficinas.id','oficinas.nombre')
        ->get();

        $cupos= Cupo::select('cupos.start','cupos.id','cupos.id_oficina')
        ->where('cupos.id_oficina','=', $cita->id)
        //->where('cupos.start','>', $cita->start)
        ->where('cupos.start','>=', $fechaActual)
        ->orderBy('cupos.start','asc')
        ->get();

        return response()->json(['ofi' => $ofi, 'cupos' => $cupos],200);
    }

    public function fechas($idcita,$idofi)
    {
        $fechaActual = date('Y-m-d');

        $cita= DetalleCupo::join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
        ->join("oficinas","oficinas.id", "=", "cupos.id_oficina")
        ->select('cupos.start','oficinas.id')
        ->where('detalle_cupos.id','=',$idcita)
        ->first();

        $cupos= Cupo::select('cupos.start','cupos.id','cupos.id_oficina')
        ->where('cupos.id_oficina','=', $idofi)
        //->where('cupos.start','>', $cita->start)
        ->where('cupos.start','>=', $fechaActual)
        ->orderBy('cupos.start','asc')
        ->get();

        return response()->json($cupos);
    }

}

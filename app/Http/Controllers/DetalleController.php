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
use Illuminate\Support\Facades\Log;
use DateTime;
use DateInterval;

use Twilio\Rest\Client;

define('WB_TOKEN', '963fe4d6878286fc02a3b4571b84162f6176c9f6c3fc4');
define('WB_FROM', '16315067068');
date_default_timezone_set("America/New_York");


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
        ->select(DB::raw('(CASE WHEN estados.nombre = "pendiente" THEN "PENDIENTE" ELSE (CASE WHEN estados.nombre = "confirmado" THEN "CONFIRMADA" ELSE (CASE WHEN estados.nombre = "cancelado" THEN "CANCELADA" ELSE (CASE WHEN estados.nombre = "no answer" THEN "NO ANSWER" END) END)  END) END) AS nombreestado'),"clientes.*","clientes.nombre as nombrec","detalle_cupos.*","detalle_cupos.id as idcita","cupos.*","cupos.id as idcupo","users.*","oficinas.*","oficinas.nombre as nombreo")    
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
            return view('welcome',compact('cliente','vista'));
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


    public function confirmar(Request $request)
    {
        
        if($request->vista == 'fisica'){

            $cita = DetalleCupo::find($request->idcita);
            $cita->id_estado = 1;
            $cita->save();  
    
            $cliente = DetalleCupo::join("clientes","clientes.id", "=", "detalle_cupos.id_cliente")
            ->join("users","users.id", "=", "detalle_cupos.id_usuario")
            ->join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
            ->join("oficinas","oficinas.id", "=", "cupos.id_oficina")
            ->select("users.name","cupos.start","clientes.telefono","clientes.id as cliente_id","clientes.nombre","oficinas.direccion","detalle_cupos.hora","detalle_cupos.id")
            ->where('detalle_cupos.id','=',$request->idcita)
            ->first();

            $direcciondecita = "La dirección de nuestra oficina es 
            📍 $cliente->direccion";

            $bitacora = DB::connection('mysql')->table('bitacoras')->insert([
                'fecha' => date('Y-m-d H:i:s'),
                'accion' => 'Cita confirmada',
                'estado' => 'confirmado',
                'usuario' => 'Cliente - '.$cliente->nombre,
                'id_cita' => $cliente->id,
            ]);

        }else if($request->vista == 'virtual'){

            $cita = DB::connection('mysql2')->table('detalle_cupos')->where('id', $request->idcita)->update(['id_estado' => 1]);

            $cliente = DB::connection('mysql2')->table('detalle_cupos')
             ->join("clientes","clientes.id", "=", "detalle_cupos.id_cliente")
             ->join("users","users.id", "=", "detalle_cupos.id_usuario")
             ->join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
             ->join("oficinas","oficinas.id", "=", "cupos.id_oficina")
             ->select("users.name","cupos.start","clientes.telefono","clientes.id as cliente_id","clientes.nombre","oficinas.direccion","detalle_cupos.hora","detalle_cupos.id")
             ->where('detalle_cupos.id','=',$request->idcita)
             ->first();

             $bitacora = DB::connection('mysql2')->table('bitacoras')->insert([
                'fecha' => date('Y-m-d H:i:s'),
                'accion' => 'Cita confirmada',
                'estado' => 'confirmado',
                'usuario' => 'Cliente - '.$cliente->nombre,
                'id_cita' => $cliente->id,
            ]);

             $direcciondecita = "";
        }

        $fechatexto= Carbon::parse($cliente->start)->locale('es')->isoformat('dddd D \d\e MMMM \d\e\l Y');

        $hora = Carbon::parse($cliente->hora)->format('h:i A');

        $horamedia = explode(" ", $hora);
        if($horamedia[1] == "PM"){
           $horatexto= $horamedia[0]." de la tarde"; 
        }else if($horamedia[1] == "AM"){
            $horatexto= $horamedia[0]." de la mañana"; 
        }

        $msg="!Hola! le saluda $cliente->name de parte de *Contigo Mortgage* 🏠✅
        
Su cita $request->vista ha sido confirmada para el día $fechatexto a las $horatexto

$direcciondecita

Los documentos requeridos para personas con social:

✅ Comprobantes de taxes del 2021
✅ Comprobantes de taxes del 2022
✅ Documento de identificación, puede ser la licencia o el pasaporte
✅ Copia de Social Security Number 
✅ Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment

*Los documentos requeridos para PERSONAS CON TAX ID:*

✅ COPIA DE SU TAX ID
✅ Documento de identificación, puede ser la licencia o el pasaporte
✅ Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment

*Documentos requeridos para el programa TAX ID 3.5 Down-payment :*

✅ Comprobantes de taxes del 2021.
✅ Comprobantes de taxes del 2022.
✅ Documento de identificación, puede ser la licencia o el pasaporte con vigencia mínima de 6 meses.
✅ Carta de TAX ID.
✅ Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment.
✅ Comprobante de renta por cualquier medio electrónico (No pagos en Cash).

¡Estos documentos son por cada persona interesada en comprar la casa!
";
/************************************************************************************** */
$msgtxt="!Hola! le saluda $cliente->name de parte de *Contigo Mortgage* 
        
Su cita $request->vista ha sido confirmada para el día $fechatexto a las $horatexto

$direcciondecita

Los documentos requeridos para personas con social:

Comprobantes de taxes del 2021
Comprobantes de taxes del 2022
Documento de identificación, puede ser la licencia o el pasaporte
Copia de Social Security Number 
Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment

*Los documentos requeridos para PERSONAS CON TAX ID:*

COPIA DE SU TAX ID
Documento de identificación, puede ser la licencia o el pasaporte
Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment

*Documentos requeridos para el programa TAX ID 3.5 Down-payment :*

Comprobantes de taxes del 2021.
Comprobantes de taxes del 2022.
Documento de identificación, puede ser la licencia o el pasaporte con vigencia mínima de 6 meses.
Carta de TAX ID.
Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment.
Comprobante de renta por cualquier medio electrónico (No pagos en Cash).

¡Estos documentos son por cada persona interesada en comprar la casa!";

        $array =str_split($cliente->telefono);
        $numeroCompleto="+1".$array[1].$array[2].$array[3].$array[6].$array[7].$array[8].$array[10].$array[11].$array[12].$array[13];

        $r = $this->link_send(+50379776604,$msg,$tipo=4); 
       // $r = $this->link_send($numeroCompleto,$msg,$tipo=3); 


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
    public function cancelar(Request $request)
    {

        if($request->vista == 'fisica'){

        $cita = DetalleCupo::find($request->idcita);
        $cita->motivo_cancelacion = $request->motivo ;
        $cita->id_estado = 2 ;
        $cita->save();   

        $usuario = DetalleCupo::join("users","users.id", "=", "detalle_cupos.id_usuario")
        ->join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
        ->join("oficinas","oficinas.id", "=", "cupos.id_oficina")
        ->join("clientes","clientes.id", "=", "detalle_cupos.id_cliente")
        ->select("clientes.telefono","clientes.id as cliente_id","clientes.nombre","detalle_cupos.id")
        ->where("detalle_cupos.id","=",$request->idcita)
        ->get()
        ->first();

        $bitacora = DB::connection('mysql')->table('bitacoras')->insert([
            'fecha' => date('Y-m-d H:i:s'),
            'accion' => 'Cita Cancelada',
            'estado' => 'cancelado',
            'usuario' => 'Cliente - '.$usuario->nombre,
            'id_cita' => $usuario->id,
        ]);

        }else if($request->vista == 'virtual'){ 

            $cita = DB::connection('mysql2')->table('detalle_cupos')
            ->where('id', $request->idcita)
            ->update([
                'id_estado' => 2,
                'motivo_cancelacion' => $request->motivo
            ]);

            $usuario = DB::connection('mysql2')->table('detalle_cupos')
             ->join("clientes","clientes.id", "=", "detalle_cupos.id_cliente")
             ->join("users","users.id", "=", "detalle_cupos.id_usuario")
             ->join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
             ->join("oficinas","oficinas.id", "=", "cupos.id_oficina")
             ->select("users.name","cupos.start","clientes.telefono","clientes.id as cliente_id","oficinas.direccion","detalle_cupos.hora","clientes.nombre","detalle_cupos.id")
             ->where('detalle_cupos.id','=',$request->idcita)
             ->first();

             $bitacora = DB::connection('mysql2')->table('bitacoras')->insert([
                'fecha' => date('Y-m-d H:i:s'),
                'accion' => 'Cita Cancelada',
                'estado' => 'cancelado',
                'usuario' => 'Cliente - '.$usuario->nombre,
                'id_cita' => $usuario->id,
            ]);
        }

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
       // $r = $this->link_send($numeroCompleto,$msg,$tipo=3); 

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
    public function listarhoras(Request $request)
    {

       /* $hora =CuposHorario::join("horarios","horarios.id", "=", "cupos_horarios.id_horario")
        ->select("cupos_horarios.id_cupo","horarios.hora12","horarios.hora24")
        ->where("cupos_horarios.id_cupo","=",$id)
        ->get();*/

        if($request->vista == 'fisica'){ 

            $sql = "SELECT h.hora24, h.hora12, 
            (select COUNT(*) from detalle_cupos dc 
             WHERE dc.id_cupo = $request->idcupo and HOUR(dc.hora) = h.hora24 and MINUTE(dc.hora) = 0 and dc.id_estado != 3 AND dc.id_estado !=2 and dc.estado_cupo IS null ) as total00,
            (select COUNT(*) from detalle_cupos dc
            WHERE dc.id_cupo = $request->idcupo and HOUR(dc.hora) = h.hora24 and MINUTE(dc.hora) = 30 and dc.id_estado != 3 AND dc.id_estado !=2 and dc.estado_cupo IS null ) as total30,
            cupos.cant_citas
            FROM cupos_horarios ch
            LEFT JOIN horarios h on ch.id_horario = h.id
            INNER JOIN cupos on cupos.id = ch.id_cupo
            WHERE ch.id_cupo = $request->idcupo";
            
            $hora = DB::select($sql);
    
            $cantCitas= Cupo::join("oficinas","oficinas.id","=","cupos.id_oficina")
                              ->select("cupos.*","oficinas.nombre as nombreoficina") 
                              ->where("cupos.id","=", $request->idcupo)
                              ->first();


            $sql = "select h.hora12,h.hora24,  
            (select COUNT(*) from detalle_cupos dc WHERE dc.id_cupo = ".$request->idcupo." and TIME_FORMAT(dc.hora, '%H:%i') = h.hora24  and dc.id_estado != 3 AND dc.id_estado !=2 and dc.estado_cupo IS null ) as total00, ch.cant_citas
            from cupos_horarios ch
            left JOIN horarios h on ch.id_horario = h.id
            WHERE ch.id_cupo = ".$request->idcupo." ;";

            $contadorHorascitas = DB::select($sql);


           return response()->json(['hora' => $hora, 'cantCitas' => $cantCitas, 'contadorHorascitas' => $contadorHorascitas],200);

            
        }else if($request->vista == 'virtual'){ 

            $hora = DB::connection('mysql2')
             ->table('cupos_horarios')
             ->selectRaw('horarios.hora24, horarios.hora12, 
                (select COUNT(*) from detalle_cupos dc 
                WHERE dc.id_cupo = ? and HOUR(dc.hora) = horarios.hora24 and MINUTE(dc.hora) = 0 and dc.id_estado != 3 AND dc.id_estado !=2 and dc.estado_cupo IS null ) as total00,
                (select COUNT(*) from detalle_cupos dc
                WHERE dc.id_cupo = ? and HOUR(dc.hora) = horarios.hora24 and MINUTE(dc.hora) = 30 and dc.id_estado != 3 AND dc.id_estado !=2 and dc.estado_cupo IS null ) as total30,
                cupos.cant_citas', [$request->idcupo, $request->idcupo])
             ->leftJoin('horarios', 'cupos_horarios.id_horario', '=', 'horarios.id')
             ->join('cupos', 'cupos.id', '=', 'cupos_horarios.id_cupo')
             ->where('cupos_horarios.id_cupo', '=', $request->idcupo)
             ->get();

             $cantCitas = DB::connection('mysql2')->table('cupos')
                ->join("oficinas","oficinas.id","=","cupos.id_oficina")
                ->select("cupos.*","oficinas.nombre as nombreoficina") 
                ->where("cupos.id","=", $request->idcupo)
                ->first();

                return response()->json(['hora' => $hora, 'cantCitas' => $cantCitas],200);

        }

    
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


  //-----------------------------------------------
     //concatena hora
     $horareagenda= $request->horaReagendar.':'.$request->minutosReagendar;

         if($request->vista == 'fisica'){

            $cita_telefono = DetalleCupo::join('clientes','clientes.id','=','detalle_cupos.id_cliente')
                                        ->select('clientes.*')
                                        ->where('detalle_cupos.id','=',$request->cita_id)
                                        ->get();

            $telefono = $cita_telefono[0]->telefono;

            Log::info($cita_telefono[0]->telefono); 

            $actualfecha = new DateTime();  
            $actualfecha->sub(new DateInterval('P1Y'));  
            $fechahaceunano = $actualfecha->format('Y-m-d');
    
            $historial = DB::select("SELECT COUNT(*) as total_registros FROM `detalle_cupos`
            INNER JOIN clientes ON clientes.id = detalle_cupos.id_cliente
            INNER JOIN estados ON estados.id = detalle_cupos.id_estado
            INNER JOIN users ON users.id = detalle_cupos.id_usuario
            INNER JOIN cupos ON cupos.id = detalle_cupos.id_cupo
            WHERE clientes.telefono = '$telefono' AND detalle_cupos.estado_cupo IS NULL AND cupos.start > '$fechahaceunano' AND detalle_cupos.id_estado IN(2,3,5);");
    
    Log::info($historial); 
    Log::info($fechahaceunano); 
    Log::info($request); 


                if( $historial[0]->total_registros >= 3){
                    return 55;
                }
    

                         
             $horarionuevo = DB::select("SELECT horarios.hora24,
                                                horarios.hora12,
                                                cupos_horarios.cant_citas AS cant_horarionuevo,
                                                cupos.cant_citas AS cant_horarioantiguo
                                        FROM cupos_horarios
                                        JOIN cupos ON cupos.id = cupos_horarios.id_cupo
                                        JOIN horarios ON horarios.id = cupos_horarios.id_horario
                                        WHERE cupos_horarios.id_cupo = $request->fechascupos ;
                                        ");

            if($horarionuevo[0]->cant_horarioantiguo != null){

                $cita = DetalleCupo::find($request->cita_id);

                $contadorCitas = DetalleCupo::join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
                -> where("detalle_cupos.id_cupo","=",$cita->id_cupo)
                ->where("detalle_cupos.hora","=",$horareagenda)
                ->where(function ($query) {
                    $query->where("detalle_cupos.id_estado","!=",3)
                        ->where("detalle_cupos.id_estado","!=",2)
                        ->where("detalle_cupos.estado_cupo","=",null);
                })
                ->count();

                if($contadorCitas >= $request->num_citas){
                    return 2 ;
                }

            }else{
                $cita = DetalleCupo::find($request->cita_id);

                $contadorCitas = DetalleCupo::join("cupos", "cupos.id", "=", "detalle_cupos.id_cupo")
                ->where("detalle_cupos.id_cupo", $cita->id_cupo)
                ->where("detalle_cupos.hora", $request->horaReagendar . ':' . $request->minutosReagendar)
                ->whereNotIn("detalle_cupos.id_estado", [2, 3])
                ->whereNull("detalle_cupos.estado_cupo")
                ->count();

                $existecupucitas = 0;
                foreach ($horarionuevo as $datos_hora) {
                    if($datos_hora->hora24 ==  $request->horaReagendar.':'.$request->minutosReagendar){ 
                       $existecupucitas++;
                    }
                }

                if ($existecupucitas == 0) {
                    return 2;     
                }

                 foreach ($horarionuevo as $datos_hora) {
                    if($datos_hora->hora24 ==  $request->horaReagendar.':'.$request->minutosReagendar){
                        if($contadorCitas >= $datos_hora->cant_horarionuevo ){
                           return 2;  
                        }
                    }
                 }
            }

        }else if($request->vista == 'virtual'){ 

            $contadorCitas = DB::connection('mysql2')
            ->table('detalle_cupos')
            ->join('cupos', 'cupos.id', '=', 'detalle_cupos.id_cupo')
            ->where('detalle_cupos.id_cupo', '=', $request->cuposid)
            ->where('detalle_cupos.hora', '=', $horareagenda)
            ->where(function ($query) {
                $query->where('detalle_cupos.id_estado', '!=', 3)
                    ->where('detalle_cupos.id_estado', '!=', 2)
                    ->where('detalle_cupos.estado_cupo', '=', null);
            })
            ->count();

            if($contadorCitas >= $request->num_citas){

                return 2 ;
            }
        }

        if($request->vista == 'fisica'){

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
                        
            $datosbitacora = DetalleCupo::join("users","users.id", "=", "detalle_cupos.id_usuario")
            ->join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
            ->join("oficinas","oficinas.id", "=", "cupos.id_oficina")
            ->join("clientes","clientes.id", "=", "detalle_cupos.id_cliente") 
            ->select("clientes.telefono","clientes.nombre","clientes.id as cliente_id","detalle_cupos.id")
            ->where("detalle_cupos.id","=",$request->cita_id)
            ->get()
            ->first();

            $datosbitacoracitanueva = DetalleCupo::join("users","users.id", "=", "detalle_cupos.id_usuario")
            ->join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
            ->join("oficinas","oficinas.id", "=", "cupos.id_oficina")
            ->join("clientes","clientes.id", "=", "detalle_cupos.id_cliente") 
            ->select("clientes.telefono","clientes.nombre","clientes.id as cliente_id","detalle_cupos.id")
            ->where("detalle_cupos.id","=",$detallecupo->id)
            ->get()
            ->first();

            $bitacora = DB::connection('mysql')->table('bitacoras')->insert([
                'fecha' => date('Y-m-d H:i:s'),
                'accion' => 'Cita reagendada',
                'estado' => 'reagendado',
                'usuario' => 'Cliente - '.$datosbitacora->nombre,
                'id_cita' => $datosbitacora->id,
            ]);
                                    
            $bitacora = DB::connection('mysql')->table('bitacoras')->insert([
                'fecha' => date('Y-m-d H:i:s'),
                'accion' => 'Cita Creada , viene reagendada',
                'estado' => 'pendiente',
                'usuario' => 'Cliente - '.$datosbitacoracitanueva->nombre,
                'id_cita' => $datosbitacoracitanueva->id,
            ]);

            $direcciondecita = "La dirección de nuestra oficina es 
            📍 $usuario->direccion";

        }else if($request->vista == 'virtual'){ 

            $cupo = DB::connection('mysql2')
            ->table('cupos')
            ->join('oficinas', 'oficinas.id', '=', 'cupos.id_oficina')
            ->select('oficinas.nombre as title', 'cupos.start', 'cupos.id')
            ->where('cupos.id', '=', $request->fechascupos)
            ->get()
            ->first();

            $detallecita = DB::connection('mysql2')
            ->table('detalle_cupos')
            ->select('detalle_cupos.*')
            ->where('id', $request->cita_id)
            ->get()
            ->first();

            $cita = DB::connection('mysql2')
            ->table('detalle_cupos')
            ->where('id', $request->cita_id)
            ->update([
            'id_estado' => 3,
            'descripcion' => "La cita ha sido reagendada para la oficina ".$cupo->title." para la fecha ". date('d-m-Y', strtotime($cupo->start))
            ]);

            $detallecupo = DB::connection('mysql2')->table('detalle_cupos')->insertGetId([
                'id_cupo' => $request->fechascupos,
                'id_cliente' => $detallecita->id_cliente,
                'id_estado' => 4,
                'id_usuario' => $detallecita->id_usuario,
                'hora' => $horareagenda,
                'descripcion' => $detallecita->descripcion
            ]);
                    
            $usuario = DB::connection('mysql2')
            ->table('detalle_cupos')
            ->join('users', 'users.id', '=', 'detalle_cupos.id_usuario')
            ->join('cupos', 'cupos.id', '=', 'detalle_cupos.id_cupo')
            ->join('oficinas', 'oficinas.id', '=', 'cupos.id_oficina')
            ->join('clientes', 'clientes.id', '=', 'detalle_cupos.id_cliente')
            ->select('users.name', 'cupos.start', 'clientes.telefono', 'oficinas.direccion')
            ->where('detalle_cupos.id_cupo', '=', $request->fechascupos)
            ->first();

               
            $datosbitacora = DB::connection('mysql2')->table('detalle_cupos')
            ->join("clientes","clientes.id", "=", "detalle_cupos.id_cliente")
            ->join("users","users.id", "=", "detalle_cupos.id_usuario")
            ->join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
            ->join("oficinas","oficinas.id", "=", "cupos.id_oficina")
            ->select("clientes.telefono","clientes.nombre","clientes.id as cliente_id","detalle_cupos.id")
            ->where('detalle_cupos.id','=',$request->cita_id)
            ->first();

            $datosbitacoracitanueva = DB::connection('mysql2')->table('detalle_cupos')
            ->join("clientes","clientes.id", "=", "detalle_cupos.id_cliente")
            ->join("users","users.id", "=", "detalle_cupos.id_usuario")
            ->join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
            ->join("oficinas","oficinas.id", "=", "cupos.id_oficina")
            ->select("clientes.telefono","clientes.nombre","clientes.id as cliente_id","detalle_cupos.id")
            ->where("detalle_cupos.id","=",$detallecupo)
            ->first();

            $bitacora1 = DB::connection('mysql2')->table('bitacoras')->insert([
                'fecha' => date('Y-m-d H:i:s'),
                'accion' => 'Cita reagendada',
                'estado' => 'reagendado',
                'usuario' => 'Cliente - '.$datosbitacora->nombre,
                'id_cita' => $datosbitacora->id,
            ]);
                                    
            $bitacora2 = DB::connection('mysql2')->table('bitacoras')->insert([
                'fecha' => date('Y-m-d H:i:s'),
                'accion' => 'Cita Creada , viene reagendada',
                'estado' => 'pendiente',
                'usuario' => 'Cliente - '.$datosbitacoracitanueva->nombre,
                'id_cita' => $datosbitacoracitanueva->id,
            ]);

            $direcciondecita = "";

        }

                $fechatexto= Carbon::parse($usuario->start)->locale('es')->isoformat('dddd D \d\e MMMM \d\e\l Y');

                $hora = Carbon::parse($horareagenda)->format('h:i A');

                $horamedia = explode(" ", $hora);
                if($horamedia[1] == "PM"){
                   $horatexto= $horamedia[0]." de la tarde"; 
                }else if($horamedia[1] == "AM"){
                    $horatexto= $horamedia[0]." de la mañana"; 
                }

            $msg="Hola! le saluda $usuario->name de parte de *Contigo Mortgage* 🏠✅

Su cita $request->vista ha sido reagendada para el día $fechatexto a las $horatexto
            
$direcciondecita 
            
Los documentos requeridos para personas con social:

✅ Comprobantes de taxes del 2021
✅ Comprobantes de taxes del 2022
✅ Documento de identificación, puede ser la licencia o el pasaporte
✅ Copia de Social Security Number 
✅ Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment

*Los documentos requeridos para PERSONAS CON TAX ID:*

✅ COPIA DE SU TAX ID
✅ Documento de identificación, puede ser la licencia o el pasaporte
✅ Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment

*Documentos requeridos para el programa TAX ID 3.5 Down-payment :*

✅ Comprobantes de taxes del 2021.
✅ Comprobantes de taxes del 2022.
✅ Documento de identificación, puede ser la licencia o el pasaporte con vigencia mínima de 6 meses.
✅ Carta de TAX ID.
✅ Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment.
✅ Comprobante de renta por cualquier medio electrónico (No pagos en Cash).

¡Estos documentos son por cada persona interesada en comprar la casa!!
            
*Por favor ayúdanos a confirmar tu asistencia a través  de este WhatsApp y atenderte de la mejor manera. Será un gusto tenerte en nuestra oficina, te esperamos.*
            
Cualquier consulta puedes llamarnos al 631-609-9108
            
Si tiene alguna duda estoy a la orden✅
            
            
Conócenos:
            
https://www.youtube.com/watch?v=UilV0wxXLaY&t=22s";
/****************************************************************************************************** */
$msgtxt="¡Hola! le saluda $usuario->name de parte de *Contigo Mortgage*

Su cita $request->vista ha sido reagendada para el día $fechatexto a las $horatexto

$direcciondecita 

Los documentos requeridos para personas con social:

Comprobantes de taxes del 2021
Comprobantes de taxes del 2022
Documento de identificación, puede ser la licencia o el pasaporte
Copia de Social Security Number 
Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment

*Los documentos requeridos para PERSONAS CON TAX ID:*

COPIA DE SU TAX ID
Documento de identificación, puede ser la licencia o el pasaporte
Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment

*Documentos requeridos para el programa TAX ID 3.5 Down-payment :*

Comprobantes de taxes del 2021.
Comprobantes de taxes del 2022.
Documento de identificación, puede ser la licencia o el pasaporte con vigencia mínima de 6 meses.
Carta de TAX ID.
Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment.
Comprobante de renta por cualquier medio electrónico (No pagos en Cash).

¡Estos documentos son por cada persona interesada en comprar la casa!

Por favor ayúdanos a confirmar tu asistencia a través de este WhatsApp https://wa.me/message/F4D3UQUHQTFAO1
y atenderte de la mejor manera. Será un gusto tenerte en nuestra oficina, te esperamos. 

Cualquier consulta puedes llamarnos al 631-609-9108

Si tiene alguna duda estoy a la orden";


            $array =str_split($usuario->telefono);
            $numeroCompleto="+1".$array[1].$array[2].$array[3].$array[6].$array[7].$array[8].$array[10].$array[11].$array[12].$array[13];
           
             $r = $this->link_send(+50379776604,$msg,$tipo=4);
          // $r = $this->link_send($numeroCompleto,$msg,$tipo=3); 

             $sid = "AC9e1475e1b32fec62e6dd712768584a72";
             $token  = "58ea12aa01f49e1965736ea94d043b24";
             $from= "+18334941535";
             $twilio = new Client($sid, $token);
             
            // $twilio->messages->create( +6318943177, ['from' => $from,'body' => $msgtxt,] );
        
            return 1 ;

        }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function oficinas(Request $request)
    {

        $fechaActual = date('Y-m-d');

        if($request->vista == 'fisica'){

            $cita= DetalleCupo::join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
            ->join("oficinas","oficinas.id", "=", "cupos.id_oficina")
            ->select('cupos.start','oficinas.id')
            ->where('detalle_cupos.id','=',$request->idcita)
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

        }else if($request->vista == 'virtual') {

            $cita = DB::connection('mysql2')
            ->table('detalle_cupos')
            ->join('cupos', 'cupos.id', '=', 'detalle_cupos.id_cupo')
            ->join('oficinas', 'oficinas.id', '=', 'cupos.id_oficina')
            ->select('cupos.start', 'oficinas.id')
            ->where('detalle_cupos.id', '=', $request->idcita)
            ->first();

            $ofi = DB::connection('mysql2')
            ->table('oficinas')
            ->join("cupos","cupos.id_oficina", "=", "oficinas.id")
            ->select('oficinas.id','oficinas.nombre')
            //->where('cupos.start','>=', $fechaActual)
            ->groupBy('oficinas.id','oficinas.nombre')
            ->get();
            
            $cupos = DB::connection('mysql2')
            ->table('cupos')
            ->select('cupos.start','cupos.id','cupos.id_oficina')
            ->where('cupos.id_oficina','=', $cita->id)
            ->where('cupos.start','>=', $fechaActual)
            ->orderBy('cupos.start','asc')
            ->get();

        }
        
        return response()->json(['ofi' => $ofi, 'cupos' => $cupos],200);
    }

    public function fechas(Request $request)
    {
        $fechaActual = date('Y-m-d');

        if($request->vista == 'fisica'){

            $cita= DetalleCupo::join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
            ->join("oficinas","oficinas.id", "=", "cupos.id_oficina")
            ->select('cupos.start','oficinas.id')
            ->where('detalle_cupos.id','=',$request->idcita)
            ->first();

            $cupos= Cupo::select('cupos.start','cupos.id','cupos.id_oficina')
            ->where('cupos.id_oficina','=', $request->oficinas)
            //->where('cupos.start','>', $cita->start)
            ->where('cupos.start','>=', $fechaActual)
            ->orderBy('cupos.start','asc')
            ->get();

        }else if($request->vista == 'virtual') { 

            $cita = DB::connection('mysql2')
            ->table('detalle_cupos')
            ->join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
            ->join("oficinas","oficinas.id", "=", "cupos.id_oficina")
            ->select('cupos.start','oficinas.id')
            ->where('detalle_cupos.id','=',$request->idcita)
            ->first();

            $cupos = DB::connection('mysql2')
            ->table('cupos')
            ->select('cupos.start','cupos.id','cupos.id_oficina')
            ->where('cupos.id_oficina','=', $request->oficinas)
            ->where('cupos.start','>=', $fechaActual)
            ->orderBy('cupos.start','asc')
            ->get();
        }

        return response()->json($cupos);
    }

}

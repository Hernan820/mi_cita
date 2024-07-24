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

date_default_timezone_set("America/New_York");

class DetalleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    // credenciales de Twilio
    protected $sid;
    protected $token;
    protected $from;

    // credenciales WAAPI
    protected $ID_WAAPI;
    protected $TOKEN_WAAPI;

    public  $tipo =3;
     
    public function encriptacion($valor){
        $encrypted_data = base64_decode($valor);
        return openssl_decrypt($valor, 'aes-256-cbc', '1234567812345678', false, '1234567812345678');
    }
 
    public function index($vista,$idc)
    {

       $idcliente =base64_decode($idc);
       $fechaActual             = new DateTime();
       $fechaAnterior           = $fechaActual->sub(new DateInterval('P12M'));
       $fecha12mesesantes       = $fechaAnterior->format('Y-m-d');

       if($vista == 'fisica'){

        $cliente = DetalleCupo::join("clientes","clientes.id", "=", "detalle_cupos.id_cliente")
        ->join("users","users.id", "=", "detalle_cupos.id_usuario")
        ->join("cupos","cupos.id","=","detalle_cupos.id_cupo")
        ->join("oficinas","oficinas.id","=","cupos.id_oficina")
        ->join("estados","estados.id","=","detalle_cupos.id_estado")
        ->select(DB::raw('(CASE WHEN estados.nombre = "pendiente" THEN "PENDIENTE" ELSE (CASE WHEN estados.nombre = "confirmado" THEN "CONFIRMADA" ELSE (CASE WHEN estados.nombre = "cancelado" THEN "CANCELADA" ELSE (CASE WHEN estados.nombre = "no answer" THEN "NO ANSWER" END) END)  END) END) AS nombreestado'),"clientes.*","clientes.nombre as nombrec","detalle_cupos.*","detalle_cupos.id as idcita","cupos.*","cupos.id as idcupo","users.*","oficinas.*","oficinas.nombre as nombreo")    
        ->where("detalle_cupos.id_cliente", "=",$idcliente)
        ->where("detalle_cupos.estado_cupo", "=",null)
        ->where("detalle_cupos.id_estado", "!=",3)
        ->where("cupos.start", ">=",$fecha12mesesantes.' 00:00:00')
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
        ->where("cupos.start", ">=",$fecha12mesesantes.' 00:00:00')
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
    public function __construct(){
        $this->sid = env('TWILIO_SID');
        $this->token = env('TWILIO_AUTH_TOKEN');
        $this->from = env('TWILIO_NUMBER');
 
        $this->ID_WAAPI = env('WAAPI_INSTANCE_ID');
        $this->TOKEN_WAAPI = env('WAAPI_API_TOKEN');
     }
     /**
      *  funcion de whatsapp
      */
     public function EnviaMessageWA($numero,$message,$tipo){
 
         if ($tipo == 3) {
 
             $curl = curl_init();
             
             curl_setopt_array($curl, [
                 CURLOPT_URL => "https://waapi.app/api/v1/instances/".$this->ID_WAAPI."/client/action/send-message",
                 CURLOPT_RETURNTRANSFER => true,
                 CURLOPT_ENCODING => "",
                 CURLOPT_MAXREDIRS => 10,
                 CURLOPT_TIMEOUT => 30,
                 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                 CURLOPT_CUSTOMREQUEST => "POST",
                 CURLOPT_POSTFIELDS => json_encode([
                 'chatId' => $numero.'@c.us',
                 'message' => $message,
                 ]),
                 CURLOPT_HTTPHEADER => [
                 "accept: application/json",
                 "authorization: Bearer ".$this->TOKEN_WAAPI,
                 "content-type: application/json"
                 ],
             ]);
             
             $response = curl_exec($curl); 
             $err = curl_error($curl);
             
             curl_close($curl);
             
             if ($err) {
                 return "cURL Error #:" . $err;
             } else {
                 return /*'Mensaje enviado true';*/  $response;
             }
         }
     }

    /**
     * separacion.
     *
     * 
     */

     public function store(Request $request){

        $horaconcatenada= $request->hora_cita;

        if($request->Id_oficina === "oficina_virtual"){

            $cita_telefono = DetalleCupo::join('clientes','clientes.id','=','detalle_cupos.id_cliente')
                                        ->select('clientes.*')
                                        ->where('detalle_cupos.id','=',$request->Id_cita)
                                        ->get();

            $telefono = $cita_telefono[0]->telefono;

            $actualfecha = new DateTime();  
            $actualfecha->sub(new DateInterval('P1Y'));  
            $fechahaceunano = $actualfecha->format('Y-m-d');
    
            $historial = DB::connection('mysql2')->select("SELECT COUNT(*) as total_registros FROM `detalle_cupos`
            INNER JOIN clientes ON clientes.id = detalle_cupos.id_cliente
            INNER JOIN estados ON estados.id = detalle_cupos.id_estado
            INNER JOIN users ON users.id = detalle_cupos.id_usuario
            INNER JOIN cupos ON cupos.id = detalle_cupos.id_cupo
            WHERE clientes.telefono = '$telefono' AND detalle_cupos.estado_cupo IS NULL AND cupos.start > '$fechahaceunano' AND detalle_cupos.id_estado IN(2,3,5);");

                if( $historial[0]->total_registros >= 10){
                    return response()->json(['validacion' => $validacion = 55, 'id_citanueva' =>  $id_citanueva = ''],200);
                }

             $horarionuevo = DB::connection('mysql2')->select("SELECT horarios.hora24,horarios.hora12,cupos_horarios.cant_citas AS cant_horarionuevo, cupos.cant_citas AS cant_horarioantiguo
                                        FROM cupos_horarios JOIN cupos ON cupos.id = cupos_horarios.id_cupo
                                        JOIN horarios ON horarios.id = cupos_horarios.id_horario
                                        WHERE cupos_horarios.id_cupo = $request->Id_cupo ;");

            if($horarionuevo[0]->cant_horarioantiguo != null){

                $contadorCitas = DB::connection('mysql2')
                    ->table('detalle_cupos')
                    ->join("cupos", "cupos.id", "=", "detalle_cupos.id_cupo")
                    ->where("detalle_cupos.id_cupo", "=", $request->Id_cupo)
                    ->where("detalle_cupos.hora", "=", $horaconcatenada.':00')
                    ->where(function ($query) {
                        $query->where("detalle_cupos.id_estado", "!=", 3)
                            ->where("detalle_cupos.id_estado", "!=", 2)
                            ->where("detalle_cupos.estado_cupo", "=", null);
                    })
                    ->count();

                if($contadorCitas >= $request->TotalCitasHora){
                    return response()->json(['validacion' => $validacion = 2, 'id_citanueva' =>  $id_citanueva = ''],200);
                }

            }else{

                $contadorCitas = DB::connection('mysql2')
                ->table('detalle_cupos')
                ->join('cupos', 'cupos.id', '=', 'detalle_cupos.id_cupo')
                ->where('detalle_cupos.id_cupo', $request->Id_cupo)
                ->where('detalle_cupos.hora', $horaconcatenada.':00')
                ->whereNotIn('detalle_cupos.id_estado', [2, 3])
                ->whereNull('detalle_cupos.estado_cupo')
                ->count();


                $existecupucitas = 0;
                foreach ($horarionuevo as $datos_hora) {
                    if($datos_hora->hora24 === $horaconcatenada){ 
                       $existecupucitas++;
                    }
                }

                if ($existecupucitas == 0) {
                    return response()->json(['validacion' => $validacion = 2, 'id_citanueva' =>  $id_citanueva = ''],200);
                }

                 foreach ($horarionuevo as $datos_hora) {
                    if($datos_hora->hora24 === $horaconcatenada ){
                        if($contadorCitas >= $datos_hora->cant_horarionuevo ){
                            return response()->json(['validacion' => $validacion = 2, 'id_citanueva' =>  $id_citanueva = ''],200);
                        }
                    }
                 }
            }

            $telefonovalida = DB::connection('mysql2')
            ->table('clientes')
            ->join('detalle_cupos', 'detalle_cupos.id_cliente', '=', 'clientes.id')
            ->join('cupos', 'cupos.id', '=', 'detalle_cupos.id_cupo')
            ->where('detalle_cupos.id_cupo', '=', $request->Id_cupo)
            ->where('clientes.telefono', '=', $telefono)
            ->where(function ($query) {
                $query->where('detalle_cupos.id_estado', '!=', 3)
                      ->where('detalle_cupos.id_estado', '!=', 2)
                      ->where('detalle_cupos.estado_cupo', '=', null);
            })
            ->count();
        
            if($telefonovalida > 0){
                return response()->json(['validacion' => $validacion = 35, 'id_citanueva' =>  $id_citanueva = ''],200);
            }

            $cupo_oficina = DB::connection('mysql2')->table('cupos')
                        ->join("oficinas", "oficinas.id", "=", "cupos.id_oficina")
                        ->where("cupos.id","=", $request->Id_cupo)
                        ->get();

            $datos_cliente = DetalleCupo::join("clientes", "clientes.id", "=", "detalle_cupos.id_cliente")
                ->where("detalle_cupos.id","=",$request->Id_cita)
                ->get();


            $cliente = DB::connection('mysql2')->table('clientes')->insertGetId([
                'nombre' => $datos_cliente[0]->nombre,
                'apellidos' => $datos_cliente[0]->apellidos,
                'direccion' => $datos_cliente[0]->direccion,
                'correo' => $datos_cliente[0]->correo,
                'telefono2' => $datos_cliente[0]->telefono2,
                'telefono' => $datos_cliente[0]->telefono,
                'estado_cliente' => $datos_cliente[0]->estado_cliente
            ]);

            $citafisicaactual = DetalleCupo::find($request->Id_cita);

            $detallecupo = DB::connection('mysql2')->table('detalle_cupos')->insertGetId([
                'id_cupo' => $request->Id_cupo,
                'id_cliente' => $cliente,
                'id_estado' => 4,
                'id_usuario' => $citafisicaactual->id_usuario,
                'hora' => $horaconcatenada.':00',
                'descripcion' => $citafisicaactual->descripcion
            ]);

            $citafisicaactual->id_estado = 3;
            $citafisicaactual->descripcion ="La cita ha sido reagendada por el cliente a ".$cupo_oficina[0]->nombre." para la fecha ". date('d-m-Y', strtotime($cupo_oficina[0]->start));
            $citafisicaactual->save(); 

            $bitacora = DB::connection('mysql')->table('bitacoras')->insert([
                'fecha' => date('Y-m-d H:i:s'),
                'accion' => 'Cita reagendada a cita virtual por el cliente',
                'estado' => 'reagendado',
                'usuario' => 'Cliente - '.$datos_cliente[0]->nombre,
                'id_cita' => $request->Id_cita,
            ]);
                                    
            $bitacora = DB::connection('mysql2')->table('bitacoras')->insert([
                'fecha' => date('Y-m-d H:i:s'),
                'accion' => 'Cita Creada por el cliente, reagendada de cita fisica ',
                'estado' => 'pendiente',
                'usuario' => 'Cliente - '.$datos_cliente[0]->nombre,
                'id_cita' => $detallecupo,
            ]);

            $idcliente =  base64_encode($cliente);
            $validacion = 1;


            $datosMensaje = DB::connection('mysql2')->table('detalle_cupos')
            ->select("detalle_cupos.hora","cupos.start","oficinas.direccion","users.name","clientes.telefono")
            ->join("cupos","cupos.id","=","detalle_cupos.id_cupo")
            ->join("oficinas","oficinas.id","=","cupos.id_oficina")
            ->join("users","users.id","=","detalle_cupos.id_usuario")
            ->join("clientes","clientes.id","=","detalle_cupos.id_cliente")
            ->where("detalle_cupos.id","=",$detallecupo)
            ->first();

            $fechatexto= Carbon::parse($datosMensaje->start)->locale('es')->isoformat('dddd D \d\e MMMM \d\e\l Y');
            $hora = Carbon::parse($datosMensaje->hora)->format('h:i A');
            $oficina_reagendada = $datosMensaje->direccion;

            $horamedia = explode(" ", $hora);
            if($horamedia[1] == "PM"){
               $horatexto= $horamedia[0]." de la tarde"; 
            }else if($horamedia[1] == "AM"){
                $horatexto= $horamedia[0]." de la mañana"; 
            }
        
            $msg="¡Hola! le saluda $datosMensaje->name de parte de *Contigo Mortgage*  🏠✅

Su cita ha sido reagendada a una cita virtual para el día $fechatexto a las $horatexto

*Los documentos requeridos para personas con social:*

✅ Comprobantes de taxes del 2022
✅ Comprobantes de taxes del 2023
✅ Documento de identificación, puede ser la licencia o el pasaporte
✅ Copia de Social Security Number 
✅ Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment

*Los documentos requeridos para PERSONAS CON TAX ID:*

✅ COPIA DE SU TAX ID
✅ Documento de identificación, puede ser la licencia o el pasaporte
✅ Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment
✅ Pasaporte (6 meses de vigencia minina)

¡Estos documentos son por cada persona interesada en comprar la casa!

*Por favor ayúdanos a confirmar tu asistencia a través de este whatsapp y atenderte de la mejor manera. Será un gusto tenerte en nuestra oficina, te esperamos.*

Cualquier consulta puedes llamarnos al 631-609-9108

Si tiene alguna duda estoy a la orden✅

Conócenos:

https://youtube.com/shorts/s50aV7Mv29s?feature=share 
            ";
                        
            $telefono = "1" . preg_replace("/[^0-9]/", "", $datosMensaje->telefono );
            $result = $this->EnviaMessageWA($telefono, $msg, $this->tipo);

            return response()->json(['validacion' => $validacion , 'id_citanueva' =>  $id_citanueva = $idcliente],200);

        }else{

           // NUEVA CITA FISICA DESDE LA CITA FISICA      
               
            $cita_telefono = DB::connection('mysql2')
            ->table('detalle_cupos')
            ->join('clientes', 'clientes.id', '=', 'detalle_cupos.id_cliente')
            ->select('clientes.*')
            ->where('detalle_cupos.id', '=', $request->Id_cita)
            ->get();

            $telefono = $cita_telefono[0]->telefono;

            $actualfecha = new DateTime();  
            $actualfecha->sub(new DateInterval('P1Y'));  
            $fechahaceunano = $actualfecha->format('Y-m-d');
    
            $historial = DB::connection('mysql')->select("SELECT COUNT(*) as total_registros FROM `detalle_cupos`
            INNER JOIN clientes ON clientes.id = detalle_cupos.id_cliente
            INNER JOIN estados ON estados.id = detalle_cupos.id_estado
            INNER JOIN users ON users.id = detalle_cupos.id_usuario
            INNER JOIN cupos ON cupos.id = detalle_cupos.id_cupo
            WHERE clientes.telefono = '$telefono' AND detalle_cupos.estado_cupo IS NULL AND cupos.start > '$fechahaceunano' AND detalle_cupos.id_estado IN(2,3,5);");

                if( $historial[0]->total_registros >= 33){
                    return response()->json(['validacion' => $validacion = 55, 'id_citanueva' =>  $id_citanueva = ''],200);
                }

             $horarionuevo = DB::connection('mysql')->select("SELECT horarios.hora24,horarios.hora12,cupos_horarios.cant_citas AS cant_horarionuevo, cupos.cant_citas AS cant_horarioantiguo
                                        FROM cupos_horarios JOIN cupos ON cupos.id = cupos_horarios.id_cupo
                                        JOIN horarios ON horarios.id = cupos_horarios.id_horario
                                        WHERE cupos_horarios.id_cupo = $request->Id_cupo ;");

            if($horarionuevo[0]->cant_horarioantiguo != null){

                $contadorCitas = DB::connection('mysql')
                    ->table('detalle_cupos')
                    ->join("cupos", "cupos.id", "=", "detalle_cupos.id_cupo")
                    ->where("detalle_cupos.id_cupo", "=", $request->Id_cupo)
                    ->where("detalle_cupos.hora", "=", $horaconcatenada.':00')
                    ->where(function ($query) {
                        $query->where("detalle_cupos.id_estado", "!=", 3)
                            ->where("detalle_cupos.id_estado", "!=", 2)
                            ->where("detalle_cupos.estado_cupo", "=", null);
                    })
                    ->count();

                if($contadorCitas >= $request->TotalCitasHora){
                    return response()->json(['validacion' => $validacion = 2, 'id_citanueva' =>  $id_citanueva = ''],200);
                }

            }else{

                $contadorCitas = DB::connection('mysql')
                ->table('detalle_cupos')
                ->join('cupos', 'cupos.id', '=', 'detalle_cupos.id_cupo')
                ->where('detalle_cupos.id_cupo', $request->Id_cupo)
                ->where('detalle_cupos.hora', $horaconcatenada.':00')
                ->whereNotIn('detalle_cupos.id_estado', [2, 3])
                ->whereNull('detalle_cupos.estado_cupo')
                ->count();

                $existecupucitas = 0;
                foreach ($horarionuevo as $datos_hora) {
                    if($datos_hora->hora24 === $horaconcatenada){ 
                       $existecupucitas++;
                    }
                }

                if ($existecupucitas == 0) {
                    return response()->json(['validacion' => $validacion = 2, 'id_citanueva' =>  $id_citanueva = ''],200);
                }

                 foreach ($horarionuevo as $datos_hora) {
                    if($datos_hora->hora24 === $horaconcatenada ){
                        if($contadorCitas >= $datos_hora->cant_horarionuevo ){
                            return response()->json(['validacion' => $validacion = 2, 'id_citanueva' =>  $id_citanueva = ''],200);
                        }
                    }
                 }
            }

            $telefonovalida = DB::connection('mysql')
            ->table('clientes')
            ->join('detalle_cupos', 'detalle_cupos.id_cliente', '=', 'clientes.id')
            ->join('cupos', 'cupos.id', '=', 'detalle_cupos.id_cupo')
            ->where('detalle_cupos.id_cupo', '=', $request->Id_cupo)
            ->where('clientes.telefono', '=', $telefono)
            ->where(function ($query) {
                $query->where('detalle_cupos.id_estado', '!=', 3)
                      ->where('detalle_cupos.id_estado', '!=', 2)
                      ->where('detalle_cupos.estado_cupo', '=', null);
            })
            ->count();
        
            if($telefonovalida > 0){
                return response()->json(['validacion' => $validacion = 35, 'id_citanueva' =>  $id_citanueva = ''],200);
            }

            $cupo_oficina = DB::connection('mysql')->table('cupos')
                        ->join("oficinas", "oficinas.id", "=", "cupos.id_oficina")
                        ->where("cupos.id","=", $request->Id_cupo)
                        ->get();

            $datos_cliente = DB::connection('mysql2')
            ->table('detalle_cupos')
            ->join('clientes', 'clientes.id', '=', 'detalle_cupos.id_cliente')
            ->where('detalle_cupos.id', '=', $request->Id_cita)
            ->get();

                $cliente = DB::connection('mysql')->table('clientes')->insertGetId([
                    'nombre' => $datos_cliente[0]->nombre,
                    'apellidos' => $datos_cliente[0]->apellidos,
                    'direccion' => $datos_cliente[0]->direccion,
                    'correo' => $datos_cliente[0]->correo,
                    'telefono2' => $datos_cliente[0]->telefono2,
                    'telefono' => $datos_cliente[0]->telefono,
                    'estado_cliente' => $datos_cliente[0]->estado_cliente
                ]);

            $detallecupo = DB::connection('mysql')->table('detalle_cupos')->insertGetId([
                'id_cupo' => $request->Id_cupo,
                'id_cliente' => $cliente,
                'id_estado' => 4,
                'id_usuario' => $datos_cliente[0]->id_usuario,
                'hora' => $horaconcatenada.':00',   
                'descripcion' => $datos_cliente[0]->descripcion
            ]);

           DB::connection('mysql2')->table('detalle_cupos')
            ->where('id', $request->Id_cita)
            ->update([
                'id_estado' => 3,
                'descripcion' => "La cita ha sido reagendada por el cliente a " . $cupo_oficina[0]->nombre . " para la fecha " . date('d-m-Y', strtotime($cupo_oficina[0]->start))
            ]);

            $bitacora = DB::connection('mysql2')->table('bitacoras')->insert([
                'fecha' => date('Y-m-d H:i:s'),
                'accion' => 'Cita reagendada a cita virtual por el cliente',
                'estado' => 'reagendado',
                'usuario' => 'Cliente - '.$datos_cliente[0]->nombre,
                'id_cita' => $request->Id_cita,
            ]);
                                    
            $bitacora = DB::connection('mysql')->table('bitacoras')->insert([
                'fecha' => date('Y-m-d H:i:s'),
                'accion' => 'Cita Creada por el cliente, reagendada de cita física ',
                'estado' => 'pendiente',
                'usuario' => 'Cliente - '.$datos_cliente[0]->nombre,
                'id_cita' => $detallecupo,
            ]);

            $datosMensaje = DB::connection('mysql')->table('detalle_cupos')
            ->select("detalle_cupos.hora","cupos.start","oficinas.direccion","users.name","clientes.telefono")
            ->join("cupos","cupos.id","=","detalle_cupos.id_cupo")
            ->join("oficinas","oficinas.id","=","cupos.id_oficina")
            ->join("users","users.id","=","detalle_cupos.id_usuario")
            ->join("clientes","clientes.id","=","detalle_cupos.id_cliente")
            ->where("detalle_cupos.id","=",$detallecupo)
            ->first();

            $fechatexto= Carbon::parse($datosMensaje->start)->locale('es')->isoformat('dddd D \d\e MMMM \d\e\l Y');
            $hora = Carbon::parse($datosMensaje->hora)->format('h:i A');
            $oficina_reagendada = $datosMensaje->direccion;

            $horamedia = explode(" ", $hora);
            if($horamedia[1] == "PM"){
               $horatexto= $horamedia[0]." de la tarde"; 
            }else if($horamedia[1] == "AM"){
                $horatexto= $horamedia[0]." de la mañana"; 
            }
        
            $msg="¡Hola! le saluda $datosMensaje->name de parte de *Contigo Mortgage*  🏠✅

Su cita ha sido reagendada a una cita física para el día $fechatexto a las $horatexto

La dirección de nuestra oficina es 
📍 $oficina_reagendada

*Los documentos requeridos para personas con social:*

✅ Comprobantes de taxes del 2022
✅ Comprobantes de taxes del 2023
✅ Documento de identificación, puede ser la licencia o el pasaporte
✅ Copia de Social Security Number 
✅ Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment

*Los documentos requeridos para PERSONAS CON TAX ID:*

✅ COPIA DE SU TAX ID
✅ Documento de identificación, puede ser la licencia o el pasaporte
✅ Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment
✅ Pasaporte (6 meses de vigencia minina)

¡Estos documentos son por cada persona interesada en comprar la casa!

*Por favor ayúdanos a confirmar tu asistencia a través de este whatsapp y atenderte de la mejor manera. Será un gusto tenerte en nuestra oficina, te esperamos.*

Cualquier consulta puedes llamarnos al 631-609-9108

Si tiene alguna duda estoy a la orden✅

Conócenos:

https://youtube.com/shorts/s50aV7Mv29s?feature=share 
            ";

            $telefono = "1" . preg_replace("/[^0-9]/", "", $datosMensaje->telefono );
            $result = $this->EnviaMessageWA($telefono, $msg, $this->tipo);

            $idcliente =  base64_encode($cliente);
            $validacion = 1;

            return response()->json(['validacion' => $validacion , 'id_citanueva' =>  $id_citanueva = $idcliente],200);
        }
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

✅ Comprobantes de taxes del 2022
✅ Comprobantes de taxes del 2023
✅ Documento de identificación, puede ser la licencia o el pasaporte
✅ Copia de Social Security Number 
✅ Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment

*Los documentos requeridos para PERSONAS CON TAX ID:*

✅ COPIA DE SU TAX ID
✅ Documento de identificación, puede ser la licencia o el pasaporte
✅ Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment
✅ Pasaporte (6 meses de vigencia minina)

¡Estos documentos son por cada persona interesada en comprar la casa!
";
/************************************************************************************** */
$msgtxt="!Hola! le saluda $cliente->name de parte de *Contigo Mortgage* 
        
Su cita $request->vista ha sido confirmada para el día $fechatexto a las $horatexto

$direcciondecita

Los documentos requeridos para personas con social:

Comprobantes de taxes del 2022
Comprobantes de taxes del 2023
Documento de identificación, puede ser la licencia o el pasaporte
Copia de Social Security Number 
Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment

*Los documentos requeridos para PERSONAS CON TAX ID:*

COPIA DE SU TAX ID
Documento de identificación, puede ser la licencia o el pasaporte
Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment
Pasaporte (6 meses de vigencia minina)

¡Estos documentos son por cada persona interesada en comprar la casa!";

    $numeroCompleto = "1" . preg_replace("/[^0-9]/", "", $cliente->telefono);
    $r = $this->EnviaMessageWA($numeroCompleto,$msg,$this->tipo); 

    try {
        $twilio = new Client($this->sid, $this->token);      
        $twilio->messages->create("+".$numeroCompleto, ['from' => $this->from,'body' => $msgtxt,] );
    } catch (\Exception $e) {
        $res_twlio = false;
        Log::error('Error en el envío de mensaje: ' . $e->getMessage());
    }
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

    $numeroCompleto = "1" . preg_replace("/[^0-9]/", "", $usuario->telefono);
    $r = $this->EnviaMessageWA($numeroCompleto,$msg,$this->tipo);

    try {
        $twilio = new Client($this->sid, $this->token);      
        $twilio->messages->create("+".$numeroCompleto, ['from' => $this->from,'body' => $msgtxt,] );
    } catch (\Exception $e) {
        $res_twlio = false;
        Log::error('Error en el envío de mensaje: ' . $e->getMessage());
    }

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

            $contadorHorascitas = DB::connection('mysql2')->select(
                "SELECT h.hora12, h.hora24,
                (SELECT COUNT(*) FROM detalle_cupos dc WHERE dc.id_cupo = ? AND TIME_FORMAT(dc.hora, '%H:%i') = h.hora24 AND dc.id_estado != 3 AND dc.id_estado != 2 AND dc.estado_cupo IS NULL) AS total00, ch.cant_citas
                FROM cupos_horarios ch
                LEFT JOIN horarios h ON ch.id_horario = h.id
                WHERE ch.id_cupo = ?",
                [$request->idcupo, $request->idcupo]
            );

            return response()->json(['hora' => $hora, 'cantCitas' => $cantCitas, 'contadorHorascitas' => $contadorHorascitas],200);
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
        //concatena hora
        $horareagenda= $request->hora_cita;

         if($request->vista == 'fisica'){

            $cita_telefono = DetalleCupo::join('clientes','clientes.id','=','detalle_cupos.id_cliente')
                                        ->select('clientes.*')
                                        ->where('detalle_cupos.id','=',$request->Id_cita)
                                        ->get();

            $telefono = $cita_telefono[0]->telefono;

            $actualfecha = new DateTime();  
            $actualfecha->sub(new DateInterval('P1Y'));  
            $fechahaceunano = $actualfecha->format('Y-m-d');
    
            $historial = DB::select("SELECT COUNT(*) as total_registros FROM `detalle_cupos`
            INNER JOIN clientes ON clientes.id = detalle_cupos.id_cliente
            INNER JOIN estados ON estados.id = detalle_cupos.id_estado
            INNER JOIN users ON users.id = detalle_cupos.id_usuario
            INNER JOIN cupos ON cupos.id = detalle_cupos.id_cupo
            WHERE clientes.telefono = '$telefono' AND detalle_cupos.estado_cupo IS NULL AND cupos.start > '$fechahaceunano' AND detalle_cupos.id_estado IN(2,3,5);");
    

                if( $historial[0]->total_registros >= 30){
                    return 55;
                }
    
             $horarionuevo = DB::select("SELECT horarios.hora24,
                                                horarios.hora12,
                                                cupos_horarios.cant_citas AS cant_horarionuevo,
                                                cupos.cant_citas AS cant_horarioantiguo
                                        FROM cupos_horarios
                                        JOIN cupos ON cupos.id = cupos_horarios.id_cupo
                                        JOIN horarios ON horarios.id = cupos_horarios.id_horario
                                        WHERE cupos_horarios.id_cupo = $request->Id_cupo ;
                                        ");

            if($horarionuevo[0]->cant_horarioantiguo != null){

                $cita = DetalleCupo::find($request->Id_cita);

                $contadorCitas = DetalleCupo::join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
                -> where("detalle_cupos.id_cupo","=",$cita->id_cupo)
                ->where("detalle_cupos.hora","=",$horareagenda)
                ->where(function ($query) {
                    $query->where("detalle_cupos.id_estado","!=",3)
                        ->where("detalle_cupos.id_estado","!=",2)
                        ->where("detalle_cupos.estado_cupo","=",null);
                })
                ->count();

                if($contadorCitas >= $request->TotalCitasHora){
                    return 2 ;
                }

            }else{
                $cita = DetalleCupo::find($request->Id_cita);

                $contadorCitas = DetalleCupo::join("cupos", "cupos.id", "=", "detalle_cupos.id_cupo")
                ->where("detalle_cupos.id_cupo", $request->Id_cupo)
                ->where("detalle_cupos.hora", $horareagenda.":00")
                ->whereNotIn("detalle_cupos.id_estado", [2, 3])
                ->whereNull("detalle_cupos.estado_cupo")
                ->count();

                $existecupucitas = 0;
                foreach ($horarionuevo as $datos_hora) {
                    if($datos_hora->hora24 ==  $horareagenda){ 
                       $existecupucitas++;
                    }
                }

                if ($existecupucitas == 0) {
                    return 2;     
                }

                 foreach ($horarionuevo as $datos_hora) {
                    if($datos_hora->hora24 ==  $horareagenda){
                        if($contadorCitas >= $datos_hora->cant_horarionuevo ){
                           return 2;  
                        }
                    }
                 }
            }

        }else if($request->vista == 'virtual'){ 

            
            $cita_telefono = DB::connection('mysql2')
            ->table('detalle_cupos')
            ->join('clientes', 'clientes.id', '=', 'detalle_cupos.id_cliente')
            ->select('clientes.*')
            ->where('detalle_cupos.id', '=', $request->Id_cita)
            ->get();
        
            $telefono = $cita_telefono[0]->telefono;

            $actualfecha = new DateTime();  
            $actualfecha->sub(new DateInterval('P1Y'));  
            $fechahaceunano = $actualfecha->format('Y-m-d');
    
            $historial = DB::connection('mysql2')->select("SELECT COUNT(*) as total_registros FROM `detalle_cupos`
            INNER JOIN clientes ON clientes.id = detalle_cupos.id_cliente
            INNER JOIN estados ON estados.id = detalle_cupos.id_estado
            INNER JOIN users ON users.id = detalle_cupos.id_usuario
            INNER JOIN cupos ON cupos.id = detalle_cupos.id_cupo
            WHERE clientes.telefono = '$telefono' AND detalle_cupos.estado_cupo IS NULL AND cupos.start > '$fechahaceunano' AND detalle_cupos.id_estado IN(2,3,5);");

            // if( $historial[0]->total_registros >= 3){
            //     return 55;
            // }

             $horarionuevo = DB::connection('mysql2')->select("SELECT horarios.hora24,horarios.hora12,cupos_horarios.cant_citas AS cant_horarionuevo, cupos.cant_citas AS cant_horarioantiguo
                                        FROM cupos_horarios JOIN cupos ON cupos.id = cupos_horarios.id_cupo
                                        JOIN horarios ON horarios.id = cupos_horarios.id_horario
                                        WHERE cupos_horarios.id_cupo = $request->Id_cupo ;");

            if($horarionuevo[0]->cant_horarioantiguo != null){

                $contadorCitas = DB::connection('mysql2')
                    ->table('detalle_cupos')
                    ->join("cupos", "cupos.id", "=", "detalle_cupos.id_cupo")
                    ->where("detalle_cupos.id_cupo", "=", $request->Id_cupo)
                    ->where("detalle_cupos.hora", "=", $horareagenda.':00')
                    ->where(function ($query) {
                        $query->where("detalle_cupos.id_estado", "!=", 3)
                            ->where("detalle_cupos.id_estado", "!=", 2)
                            ->where("detalle_cupos.estado_cupo", "=", null);
                    })
                    ->count();

                if($contadorCitas >= $request->TotalCitasHora){
                    return 2;
                }

            }else{

                $contadorCitas = DB::connection('mysql2')
                ->table('detalle_cupos')
                ->join('cupos', 'cupos.id', '=', 'detalle_cupos.id_cupo')
                ->where('detalle_cupos.id_cupo', $request->Id_cupo)
                ->where('detalle_cupos.hora', $horareagenda.':00')
                ->whereNotIn('detalle_cupos.id_estado', [2, 3])
                ->whereNull('detalle_cupos.estado_cupo')
                ->count();

                $existecupucitas = 0;
                foreach ($horarionuevo as $datos_hora) {
                    if($datos_hora->hora24 === $horareagenda){ 
                       $existecupucitas++;
                    }
                }

                if ($existecupucitas == 0) {
                    return 2;
                }

                 foreach ($horarionuevo as $datos_hora) {
                    if($datos_hora->hora24 === $horareagenda ){
                        if($contadorCitas >= $datos_hora->cant_horarionuevo ){
                            return 2;
                        }
                    }
                 }
            }

            $telefonovalida = DB::connection('mysql2')
            ->table('clientes')
            ->join('detalle_cupos', 'detalle_cupos.id_cliente', '=', 'clientes.id')
            ->join('cupos', 'cupos.id', '=', 'detalle_cupos.id_cupo')
            ->where('detalle_cupos.id_cupo', '=', $request->Id_cupo)
            ->where('clientes.telefono', '=', $telefono)
            ->where(function ($query) {
                $query->where('detalle_cupos.id_estado', '!=', 3)
                      ->where('detalle_cupos.id_estado', '!=', 2)
                      ->where('detalle_cupos.estado_cupo', '=', null);
            })  
            ->count();

            if($telefonovalida > 0){
                return 35;
            }
        }

        if($request->vista == 'fisica'){

            $cupo = Cupo::join("oficinas","oficinas.id", "=", "cupos.id_oficina")
            ->select("oficinas.nombre as title", "cupos.start","cupos.id")
            ->where("cupos.id", "=", $request->Id_cupo)
            ->get()
            ->first();
    
           //reagenda la cita
            $cita = DetalleCupo::find($request->Id_cita);
    
            //crea una cita con las mismas propiedades
            $detallecupo= new DetalleCupo;
            $detallecupo->id_cupo = $request->Id_cupo;
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
            ->where("detalle_cupos.id_cupo","=",$request->Id_cupo)
            ->get()
            ->first();
                        
            $datosbitacora = DetalleCupo::join("users","users.id", "=", "detalle_cupos.id_usuario")
            ->join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
            ->join("oficinas","oficinas.id", "=", "cupos.id_oficina")
            ->join("clientes","clientes.id", "=", "detalle_cupos.id_cliente") 
            ->select("clientes.telefono","clientes.nombre","clientes.id as cliente_id","detalle_cupos.id")
            ->where("detalle_cupos.id","=",$request->Id_cita)
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
            ->where('cupos.id', '=', $request->Id_cupo)
            ->get()
            ->first();

            $detallecita = DB::connection('mysql2')
            ->table('detalle_cupos')
            ->select('detalle_cupos.*')
            ->where('id', $request->Id_cita)
            ->get()
            ->first();

            $cita = DB::connection('mysql2')
            ->table('detalle_cupos')
            ->where('id', $request->Id_cita)
            ->update([
            'id_estado' => 3,
            'descripcion' => "La cita ha sido reagendada para la oficina ".$cupo->title." para la fecha ". date('d-m-Y', strtotime($cupo->start))
            ]);

            $detallecupo = DB::connection('mysql2')->table('detalle_cupos')->insertGetId([
                'id_cupo' => $request->Id_cupo,
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
            ->where('detalle_cupos.id_cupo', '=', $request->Id_cupo)
            ->first();

               
            $datosbitacora = DB::connection('mysql2')->table('detalle_cupos')
            ->join("clientes","clientes.id", "=", "detalle_cupos.id_cliente")
            ->join("users","users.id", "=", "detalle_cupos.id_usuario")
            ->join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
            ->join("oficinas","oficinas.id", "=", "cupos.id_oficina")
            ->select("clientes.telefono","clientes.nombre","clientes.id as cliente_id","detalle_cupos.id")
            ->where('detalle_cupos.id','=',$request->Id_cita)
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

✅ Comprobantes de taxes del 2022
✅ Comprobantes de taxes del 2023
✅ Documento de identificación, puede ser la licencia o el pasaporte
✅ Copia de Social Security Number 
✅ Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment

*Los documentos requeridos para PERSONAS CON TAX ID:*

✅ COPIA DE SU TAX ID
✅ Documento de identificación, puede ser la licencia o el pasaporte
✅ Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment
✅ Pasaporte (6 meses de vigencia minina)

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

Comprobantes de taxes del 2022
Comprobantes de taxes del 2023
Documento de identificación, puede ser la licencia o el pasaporte
Copia de Social Security Number 
Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment

*Los documentos requeridos para PERSONAS CON TAX ID:*

COPIA DE SU TAX ID
Documento de identificación, puede ser la licencia o el pasaporte
Los últimos 3 estado de cuenta bancario donde se refleje el Down-payment
Pasaporte (6 meses de vigencia minina)

¡Estos documentos son por cada persona interesada en comprar la casa!

Por favor ayúdanos a confirmar tu asistencia a través de este WhatsApp https://wa.me/message/F4D3UQUHQTFAO1
y atenderte de la mejor manera. Será un gusto tenerte en nuestra oficina, te esperamos. 

Cualquier consulta puedes llamarnos al 631-609-9108

Si tiene alguna duda estoy a la orden";

    $numeroCompleto = "1" . preg_replace("/[^0-9]/", "", $usuario->telefono);
    $r = $this->EnviaMessageWA($numeroCompleto,$msg,$this->tipo);

    try {
        $twilio = new Client($this->sid, $this->token);      
        $twilio->messages->create("+".$numeroCompleto, ['from' => $this->from,'body' => $msgtxt,] );
    } catch (\Exception $e) {
        $res_twlio = false;
        Log::error('Error en el envío de mensaje: ' . $e->getMessage());
    }
        
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

        $fechaActual = date('Y-m-d').' 00:00:00';

        if($request->vista == 'fisica'){

            $cita= DetalleCupo::join("cupos","cupos.id", "=", "detalle_cupos.id_cupo")
            ->join("oficinas","oficinas.id", "=", "cupos.id_oficina")
            ->select('cupos.start','oficinas.id')
            ->whereNull('cupos.estado_cupo')

            ->where('detalle_cupos.id','=',$request->idcita)
            ->first();
    
            $ofi= Oficina::join("cupos","cupos.id_oficina", "=", "oficinas.id")
            ->select('oficinas.id','oficinas.nombre')
            ->whereNull('cupos.estado_cupo')

            ->where('cupos.start','>=', $fechaActual)
            //->where('cupos.start','>=', $cita->start)
            ->groupBy('oficinas.id','oficinas.nombre')
            ->get();
    
            $cupos= Cupo::select('cupos.start','cupos.id','cupos.id_oficina')
            ->where(function ($query) use ($cita,$fechaActual) {
                $query->where('cupos.id_oficina','=', $cita->id)
                ->where('cupos.start','>', $cita->start)
                ->where('cupos.start','>', $fechaActual)
                ->whereNull('cupos.estado_cupo');
            })
            ->orderBy('cupos.start','asc')
            ->get();

            $ofifisica="";

        }else if($request->vista == 'virtual') {

            $cita = DB::connection('mysql2')
            ->table('detalle_cupos')
            ->join('cupos', 'cupos.id', '=', 'detalle_cupos.id_cupo')
            ->join('oficinas', 'oficinas.id', '=', 'cupos.id_oficina')
            ->select('cupos.start', 'oficinas.id')
            ->whereNull('cupos.estado_cupo')

            ->where('detalle_cupos.id', '=', $request->idcita)
            ->first();

            $ofi = DB::connection('mysql2')
            ->table('oficinas')
            ->join("cupos","cupos.id_oficina", "=", "oficinas.id")
            ->select('oficinas.id','oficinas.nombre')
            ->where('cupos.start','>=', $fechaActual)
            ->whereNull('cupos.estado_cupo')
            ->groupBy('oficinas.id','oficinas.nombre')
            ->get();
            
            $cupos = DB::connection('mysql2')
            ->table('cupos')
            ->select('cupos.start','cupos.id','cupos.id_oficina')
            ->where(function ($query) use ($cita,$fechaActual) {
                $query->where('cupos.id_oficina','=', $cita->id)
                ->where('cupos.start','>', $fechaActual)
                ->whereNull('cupos.estado_cupo');
            })
            ->orderBy('cupos.start','asc')
            ->get();

            $ofifisica= Oficina::join("cupos","cupos.id_oficina", "=", "oficinas.id")
            ->select('oficinas.id','oficinas.nombre')
            ->where('cupos.start','>=', $fechaActual)
            ->whereNull('cupos.estado_cupo')
            //->where('cupos.start','>=', $cita->start)
            ->groupBy('oficinas.id','oficinas.nombre')
            ->get();

        }
        
        return response()->json(['ofi' => $ofi, 'cupos' => $cupos, 'ofifisica' => $ofifisica ],200);
    }

    public function fechas(Request $request)
    {
        $fechaActual = date('Y-m-d');
        if($request->oficinas === 'oficina_virtual'){
            $cupos = DB::connection('mysql2')
            ->table('cupos')
            ->select('cupos.start','cupos.id','cupos.id_oficina')
            // ->where('cupos.id_oficina','=', $request->oficinas)
            ->where('cupos.start','>=', $fechaActual)
            ->whereNull('cupos.estado_cupo')
            ->orderBy('cupos.start','asc')
            ->get();
            return response()->json($cupos);
        }   

        if($request->vista == 'fisica'){

            $cupos= Cupo::select('cupos.start','cupos.id','cupos.id_oficina')
            ->where('cupos.id_oficina','=', $request->oficinas)
            //->where('cupos.start','>', $cita->start)
            ->whereNull('cupos.estado_cupo')
            ->where('cupos.start','>=', $fechaActual)
            ->orderBy('cupos.start','asc')
            ->get();

        }else if($request->vista == 'virtual') { 

            $cupos = DB::connection('mysql2')
            ->table('cupos')
            ->select('cupos.start','cupos.id','cupos.id_oficina')
            ->whereNull('cupos.estado_cupo')
            ->where('cupos.id_oficina','=', $request->oficinas)
            ->where('cupos.start','>=', $fechaActual)
            ->orderBy('cupos.start','asc')
            ->get();
        }

        return response()->json($cupos);
    }
    /**
     * 
     */
    function horasdecupo($id,$oficina){

        if ($oficina == "oficina_virtual") {

            $horarionuevo = DB::connection('mysql2')->select("select h.hora12,h.hora24,  
            (select COUNT(*) from detalle_cupos dc WHERE dc.id_cupo = ".$id." and TIME_FORMAT(dc.hora, '%H:%i') = h.hora24  and dc.id_estado != 3 AND dc.id_estado !=2 and dc.estado_cupo IS null ) as total00, ch.cant_citas
            from cupos_horarios ch
            left JOIN horarios h on ch.id_horario = h.id
            WHERE ch.id_cupo = ".$id." ;");

        }else{

            $horarionuevo = DB::select("select h.hora12,h.hora24,  
            (select COUNT(*) from detalle_cupos dc WHERE dc.id_cupo = ".$id." and TIME_FORMAT(dc.hora, '%H:%i') = h.hora24  and dc.id_estado != 3 AND dc.id_estado !=2 and dc.estado_cupo IS null ) as total00, ch.cant_citas
            from cupos_horarios ch
            left JOIN horarios h on ch.id_horario = h.id
            WHERE ch.id_cupo = ".$id." ;");
    
        }


        return response()->json($horarionuevo);
    }
}

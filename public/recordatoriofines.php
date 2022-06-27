<?php

$usuario = "root";
$contrasena = "";  
$servidor = "localhost";
$basededatos = "agenda";
define('WB_TOKEN', '963fe4d6878286fc02a3b4571b84162f6176c9f6c3fc4');
define('WB_FROM', '16315067068');
date_default_timezone_set("America/New_York");
//setlocale(LC_TIME, "spanish");


$conexion1 = mysqli_connect( $servidor, $usuario, "" ) or die ("No se ha podido conectar al servidor de Base de datos");
$db1 = mysqli_select_db( $conexion1, $basededatos ) or die ( "Upps! Pues va a ser que no se ha podido conectar a la base de datos" );


// OBTIENE EL FINDESEMANA MAS PROXIMO
$sabado = date('Y-m-d', strtotime("next saturday"));
$domingo = date('Y-m-d', strtotime("next sunday"));

// OBTIENE LA FECHA DEL DIA SIGUIENTE
$dia_siguiente =date("Y-m-d", strtotime("+1 day"));

// DIA DE AHORA 
//$ahora =strftime("%A");
$ahora =date('2022-06-22 w');

    $consulta = "SELECT clientes.telefono  FROM detalle_cupos
    INNER JOIN clientes on clientes.id = detalle_cupos.id_cliente
    INNER JOIN cupos on cupos.id = detalle_cupos.id_cupo
    WHERE cupos.start IN('$ahora') AND detalle_cupos.id_estado IN(4,5);";

$numeros_clientes = mysqli_query( $conexion1, $consulta ) or die ( "Algo ha ido mal en la consulta a la base de datos111111111111");

echo "ESTA ES LA FECHA ... $ahora.  </br>";

 function link_send($to,$url,$tipo)
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
};


$msg="Hola Buenas Noches  nombre cliente! le saluda $usuario->name de parte del Team Acevedo y Casa de Mis Sueños 🏠✅

El motivo de nuestro mensaje , es por que uste tiene agendando una cita con nosotros para el dia ***** a la *****

haciendo click en el siguiente enlace puedes GESTIONAR a (confirmar , cancelar o reagendar).

link

si tienes alguna consulta puedes comunicarte con nosotros al 631-609-9108


$conta";


  $conta=0;


  foreach ($numeros_clientes as $num) 
    {
        $array =str_split($num['telefono']);
        $numeroCompleto="+1".$array[1].$array[2].$array[3].$array[6].$array[7].$array[8].$array[10].$array[11].$array[12].$array[13];
        
        $r = link_send(+50379776604,$msg,$tipo=4);


        $conta=$conta + 1;
        echo '<td> '.$num['telefono'].'</td></br>';

    }
?>
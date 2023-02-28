<?php
if (strlen(session_id()) < 1)
  session_start();
require_once "../modelos/Venta.php";

$venta = new Venta();
// condicion de una sola linea
$idventa = isset($_POST["idventa"])? limpiarCadena($_POST["idventa"]):"";
$idcliente = isset($_POST["idcliente"])? limpiarCadena($_POST["idcliente"]):"";
$idusuario = $_SESSION["idusuario"];
$tipo_comprobante = isset($_POST["tipo_comprobante"])? limpiarCadena($_POST["tipo_comprobante"]):"";
$serie_comprobante = isset($_POST["serie_comprobante"])? limpiarCadena($_POST["serie_comprobante"]):"";
$num_comprobante = isset($_POST["num_comprobante"])? limpiarCadena($_POST["num_comprobante"]):"";
$fecha_hora = isset($_POST["fecha_hora"])? limpiarCadena($_POST["fecha_hora"]):"";
$impuesto = isset($_POST["impuesto"])? limpiarCadena($_POST["impuesto"]):"";
$total_venta = isset($_POST["total_venta"])? limpiarCadena($_POST["total_venta"]):"";

//op significa Operacion
switch($_GET["op"]){
    case 'guardaryeditar':
        if(empty($idventa)){
            $rspta=$venta->insertar($idcliente,$idusuario,$tipo_comprobante,$serie_comprobante,$num_comprobante,$fecha_hora,$impuesto,$total_venta,$_POST["idarticulo"],$_POST["cantidad"],$_POST["precio_venta"],$_POST["descuento"]);
            echo $rspta ? "Venta registrada" : "No se registraron todos los datos de la venta satisfactoriamente";
        }
        else {
        }
    break;
    case 'anular':
    
        $rspta=$venta->anular($idventa);
        echo $rspta ? "Venta anulada" : "Venta no se puedo anular";
    break;

    case 'mostrar':
        $rspta=$venta->mostrar($idventa);
        echo json_encode($rspta);
    break;
    case 'listarDetalle':
        //Obtiene el idventa
        $id=$_GET['id']; 

        $rspta = $venta->listarDetalle($id);
        $total=0;
        echo '
        <thead style="background-color:#A9D0F5">
        <th>Opciones</th>
        <th>Artículo</th>
        <th>Cantidad</th>
        <th>Precio Venta</th>
        <th>Descuento</th>
        <th>Subtotal</th>
        </thead>';
        while ($reg = $rspta->fetch_object())
        {
            echo '<tr class="filas"><td></td><td>'.$reg->nombre. ' - '. $reg->iddetalle_venta.'</td><td>'.$reg->cantidad.'</td><td>'.$reg->precio_venta.'</td><td>'.$reg->descuento.'</td><td>'.$reg->subtotal.'</td></tr>';
            $total =$total+($reg->precio_venta*$reg->cantidad-$reg->descuento);
        }
        echo '
        <tfoot>
            <th>TOTAL</th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th><h4 id="total">S/ '. $total.'</h4> <input type="hidden" name="total_venta" id="total_venta"> </th>
        </tfoot>';
    break;
    case 'listar':
        $rspta=$venta->listar();
        $data = Array();
        while ($reg=$rspta->fetch_object()){
            if($reg->tipo_comprobante=='Ticket')
            {
                $url='../reportes/exTicket.php?id=';
            }
            else{
                $url='../reportes/exFactura.php?id=';

            }
            $data[]=array(
                "0"=>($reg->estado=='Aceptado')?'<button class="btn btn-warning" onclick="mostrar('.$reg->idventa.')"><i class="fa fa-eye"></i></button>'.
                    ' <button class="btn btn-danger" onclick="anular('.$reg->idventa.')"><i class="fa fa-close"></i></button>':
                    ' <button class="btn btn-danger" onclick="anular('.$reg->idventa.')"><i class="fa fa-close"></i></button>'.'<button class="btn btn-warning" onclick="mostrar('.$reg->idventa.')"><i class="fa fa-eye"></i></button>',
 
                "1"=>$reg->fecha,
                "2"=>$reg->cliente . " - ".$reg->idventa,
                "3"=>$reg->usuario,
                "4"=>$reg->tipo_comprobante,
                "5"=>$reg->serie_comprobante. '-' .$reg->num_comprobante,
                "6"=>number_format($reg->total_venta,2,SPD,SPM),
                "7"=>($reg->estado=='Aceptado')?'<span class="label bg-green">Aceptado</span>':
                '<span class="label bg-red">Anulado</span>'
            );

        }
        $results= array(
            "sEcho"=>1, //info para datatables
            "iTotalRecords"=>count($data),
            "iTotalDisplayRecords"=>count($data),
            "aaData"=>$data);
        echo json_encode($results);
    break;
    case 'selectCliente':
        require_once "../modelos/Persona.php";
        $persona = new Persona();
        $rspta = $persona->listarC();
        echo '<option value="0" >Seleccione</option>';
        echo '<option value="0">Todos</option>';
        while ($reg = $rspta->fetch_object())
            {
              echo '<option value=' . $reg->idpersona . '>' . $reg->nombre . '</option>';
            }
    break;
    
    case 'listarArticulosVenta':
    require_once "../modelos/Articulo.php";
    $articulo=new Articulo();
    $rspta=$articulo->listarActivosVenta();
    $data = Array();
    while ($reg=$rspta->fetch_object()){
        $data[]=array(         
            "0"=>'<button class="btn btn-warning" onclick="agregarDetalle('.$reg->idarticulo.',\''.$reg->nombre.'\',\''.$reg->precio_venta.'\')"><span class="fa fa-plus"></span></button>',
            "1"=>$reg->nombre,
            "2"=>$reg->categoria,
            "3"=>$reg->codigo,
            "4"=>$reg->stock,
            "5"=>$reg->precio_venta,
            "6"=>'<img width="50" height="50" src="../files/articulos/'.$reg->imagen.'">'
        );

    }
    $results= array(
        "sEcho"=>1, //info para datatables
        "iTotalRecords"=>count($data),
        "iTotalDisplayRecords"=>count($data),
        "aaData"=>$data);
    echo json_encode($results);   

    break;
}
?>
<?php
require_once './app/models/venta.php';
require_once './app/models/criptomoneda.php';
require_once "./app/mw/AutentificadorJWT.php";
require_once 'IApiUsable.php';

use \App\Models\Venta as Venta;
use \App\Models\Usuario as Usuario;
use \App\Models\Criptomoneda as Criptomoneda;

class ventaCriptoController implements IApiUsable
{
    public function TraerUno($request, $response, $args) {
        $idVenta=$args['id'];
        $venta = Venta::where('id', $idVenta)->first();
        $payload = json_encode($venta);
        $response->getBody()->write($payload);
        return $response
         ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {
        $lista = Venta::all();
        $payload = json_encode(array("listaVentas" => $lista));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodosNacionalidad($request, $response, $args) {
        $nacionalidad=$args['nacionalidad'];
        $criptomoneda=Criptomoneda::where('nacionalidad', $nacionalidad)->first();
        $lista = Venta::where('id_criptomoneda', $criptomoneda->id)
                            ->where('fecha_de_compra', '>', '2021-06-09')
                            ->where('fecha_de_compra', '<', '2021-06-14')->get();                   
        $payload = json_encode(array("listaVentasNacionalidad" => $lista));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodosNombres($request, $response, $args) {
        $nombre=$args['nombre'];
        $criptomoneda=Criptomoneda::where('nombre', $nombre)->first();
        $lista = Venta::where('id_criptomoneda', '=', $criptomoneda->id)->get();
        $payload = json_encode(array("listaVentas" => $lista));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function CargarUno($request, $response, $args) {
        $parametros = $request->getParsedBody();
        $idCriptmoneda = $parametros['idCriptomoneda'];
        $cantidad = $parametros['cantidad'];
        $fechaCompra = date("Y-m-d");
        //Obtenemos usuario
        $header = $request->getHeaderLine('Authorization');
	    $token = trim(explode("Bearer", $header)[1]);
        $data = AutentificadorJWT::ObtenerData($token);	
        $usuario = Usuario::where('mail','=', $data->usuario)->first();
        if($usuario != null)
        {
            $criptomoneda = Criptomoneda::where('id', $idCriptmoneda)->first();
            if($criptomoneda != null)
            {
                // Creamos la venta
                $ruta = $criptomoneda->nombre. "+" .$criptomoneda->nacionalidad .".jpg";
                $venta = new Venta();
                $venta->id_criptomoneda = $idCriptmoneda;
                $venta->id_cliente = $usuario->id;
                $venta->cantidad = $cantidad;
                $venta->total ="$" . $criptomoneda->precio * $cantidad;
                $venta->fecha_de_compra = $fechaCompra;
                $venta->foto = "./app/fotoVentas/" . $usuario->nombre ."+". $venta->fecha_de_compra . ".jpg";
                copy($criptomoneda->foto,$venta->foto);
                $venta->save();
                $payload = json_encode(array("mensaje" => "Venta creada con exito"));
            }else
            {
                $payload = json_encode(array("mensaje" => "No existe la criptomoneda o no hay stock"));
            }
        }else
        {
            $payload = json_encode(array("mensaje" => "No existe el empleado"));
        }
        

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args) {
        $idCriptmoneda = $args['id'];
        $usuario = Criptomoneda::find($idCriptmoneda);
        $usuario->delete();
        $payload = json_encode(array("mensaje" => "Criptomoneda borrada con exito"));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args) {
        $parametros = $request->getParsedBody();
        $idCriptmonedaModificado = $parametros['idCriptomoneda'];
        $idClienteModificado = $parametros['idCliente'];
        $cantidadModificada = $parametros['cantidad'];
        $idVenta = $parametros['id'];
        //$claveModificada = $parametros['clave'];
        // Conseguimos el objeto
        $venta = Venta::where('id', '=', $idVenta)->first();
        $criptomoneda = Criptomoneda::where('id', '=', $idClienteModificado)->first();
        // Si existe
        if ($venta !== null) {
            $venta->id_criptomoneda = $idCriptmonedaModificado;
            $venta->id_cliente = $idClienteModificado;
            $venta->cantidad = $cantidadModificada;
            $venta->total ="$".$cantidadModificada * $criptomoneda->precio;
            $venta->fecha_de_compra = date("Y-m-d");
            $venta->save();
            copy($venta->foto, $venta->foto);
            $payload = json_encode(array("mensaje" => "Criptomoneda modificada con exito"));
        } else {
            $payload = json_encode(array("mensaje" => "Criptomoneda no encontrada"));
        }
        $response->getBody()->write($payload);
        return $response
        ->withHeader('Content-Type', 'application/json');	
    }
    


}

?>
<?php
require_once './app/models/criptomoneda.php';
require_once "./app/mw/AutentificadorJWT.php";
require_once 'IApiUsable.php';

use \App\Models\Criptomoneda as Criptomoneda;

class CriptomonedaController implements IApiUsable
{
    public function TraerUno($request, $response, $args) {
        $idMoneda=$args['id'];
        $criptomoneda = Criptomoneda::where('id', $idMoneda)->first();
        $payload = json_encode($criptomoneda);
        $response->getBody()->write($payload);
        return $response
         ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {
        $lista = Criptomoneda::all();
        $payload = json_encode(array("listaCriptomonedas" => $lista));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodasPorNacionalidad($request, $response, $args) {
        $nacionalidad=$args['nacionalidad'];
        $lista = Criptomoneda::where('nacionalidad','=',$nacionalidad)->get();
        $payload = json_encode(array("listaCriptomonedas" => $lista));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function CargarUno($request, $response, $args) {
        $parametros = $request->getParsedBody();
        $nombre = $parametros['nombre'];
        $precio = $parametros['precio'];
        $nacionalidad = $parametros['nacionalidad'];
        // Creamos el usuario
        $criptomoneda = new Criptomoneda();
        $criptomoneda->nombre = $nombre;
        $criptomoneda->precio = $precio;
        $criptomoneda->nacionalidad = $nacionalidad;
        $criptomoneda->foto = "./app/fotoCriptomoneda/" . $criptomoneda->nombre ."+". $criptomoneda->nacionalidad . ".jpg";
        move_uploaded_file($_FILES["foto"]["tmp_name"],$criptomoneda->foto);
        $criptomoneda->save();
        $payload = json_encode(array("mensaje" => "Criptmoneda creada con exito"));
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
        $nombreModificado = $parametros['nombre'];
        $precioModificado = $parametros['precio'];
        $nacionalidadModificada = $parametros['nacionalidad'];
        $idCriptmoneda = $parametros['id'];
        //$claveModificada = $parametros['clave'];
        // Conseguimos el objeto
        $criptomoneda = Criptomoneda::where('id', '=', $idCriptmoneda)->first();
        // Si existe
        if ($criptomoneda !== null) {
            $criptomoneda->nombre = $nombreModificado;
            $criptomoneda->precio = $precioModificado;
            $criptomoneda->nacionalidad = $nacionalidadModificado;
            $criptomoneda->save();
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
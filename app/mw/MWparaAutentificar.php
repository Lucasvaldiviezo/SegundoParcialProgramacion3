<?php
require_once "AutentificadorJWT.php";
require_once "./app/controllers/usuarioController.php";

use \App\Models\Usuario as Usuario;

class MWparaAutentificar
{
 /**
   * @api {any} /MWparaAutenticar/  Verificar Usuario
   * @apiVersion 0.1.0
   * @apiName VerificarUsuario
   * @apiGroup MIDDLEWARE
   * @apiDescription  Por medio de este MiddleWare verifico las credeciales antes de ingresar al correspondiente metodo 
   *
   * @apiParam {ServerRequestInterface} request  El objeto REQUEST.
 * @apiParam {ResponseInterface} response El objeto RESPONSE.
 * @apiParam {Callable} next  The next middleware callable.
   *
   * @apiExample Como usarlo:
   *    ->add(\MWparaAutenticar::class . ':VerificarUsuario')
   */
  public function VerificarLogin($request, $response, $next)
  {
	$respuesta = "Credenciales invalidas";
	$ArrayDeParametros = $request->getParsedBody();
	$mail=$ArrayDeParametros["mail"];
	$clave=$ArrayDeParametros["clave"];

	$lista = Usuario::all();
	foreach($lista as $usuario)
	{
		if($usuario->mail == $mail && $usuario->clave == $clave)
		{
			$datos = array('usuario' => $mail,'perfil' => $usuario->tipo, 'clave' => $clave);
			$token= AutentificadorJWT::CrearToken($datos);
			$respuesta = $token;
			echo "OK " . $usuario->tipo . "\n";
			break;
		}
	} 
	echo $respuesta;
  }
  
  public function VerificarUsuario($request, $response, $next) {
	$objDelaRespuesta= new stdclass();
	$objDelaRespuesta->respuesta="";
	if($request->isGet())
	{
		$ruta = $_SERVER['PATH_INFO'];
		if(strpos($ruta, 'criptomoneda'))
		{
			$numeros = array("0","1","2","3","4","5","6","7","8","9");
			$bool = false;
			foreach($numeros as $num)
			{
				if(str_contains($ruta, $num) != false)
				{
					$bool = true;
					break;
				}
			}
			if($bool == true)
			{
				$header = $request->getHeaderLine('Authorization');
				$token = trim(explode("Bearer", $header)[1]);	 
				$objDelaRespuesta= new stdclass();
				$objDelaRespuesta->respuesta="";
				try 
				{
					AutentificadorJWT::verificarToken($token);
					$objDelaRespuesta->esValido=true;      
				}
				catch (Exception $e) {      
					$objDelaRespuesta->excepcion=$e->getMessage();
					$objDelaRespuesta->esValido=false;     
				}
				if($objDelaRespuesta->esValido)
				{ 
					$parametros = $request->getParsedBody();
					$payload=AutentificadorJWT::ObtenerData($token);
					$response = $next($request, $response);
				}else
				{
					$objDelaRespuesta->respuesta="Solo usuarios registrados";
				}
				
			}else
			{
				
				$response = $next($request, $response);
			}	
		}else if(strpos($ruta, 'venta'))
		{
			$header = $request->getHeaderLine('Authorization');
			$token = trim(explode("Bearer", $header)[1]);	 
			$objDelaRespuesta= new stdclass();
			$objDelaRespuesta->respuesta="";
			$payload=AutentificadorJWT::ObtenerData($token);
			if($payload->perfil=="admin")
			{
				$response = $next($request, $response);
			}else
			{
				$objDelaRespuesta->respuesta="Solo admin";
			}
			
		}else
		{
			$response = $next($request, $response);
		}
	}else if($request->isPost() || $request->isPut())
	{
		$header = $request->getHeaderLine('Authorization');
		$token = trim(explode("Bearer", $header)[1]);	 
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->respuesta="";
		$ruta = $_SERVER['PATH_INFO'];
		if(str_contains($ruta, 'criptomoneda') && $request->isPost())
		{
			$parametros = $request->getParsedBody();
			$payload=AutentificadorJWT::ObtenerData($token);
			if($payload->perfil=="admin")
			{
				$response = $next($request, $response);
			}else
			{
				$objDelaRespuesta->respuesta="Solo admin";
			}
		}else
		{
			$response = $next($request, $response);
		}
		
	}else if($request->isDelete())
	{
		$header = $request->getHeaderLine('Authorization');
		$token = trim(explode("Bearer", $header)[1]);	 
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->respuesta="";
		$ruta = $_SERVER['PATH_INFO'];
		if(str_contains($ruta, 'criptomoneda'))
		{
			$parametros = $request->getParsedBody();
			$payload=AutentificadorJWT::ObtenerData($token);
			if($payload->perfil=="admin")
			{
				$response = $next($request, $response);
			}else
			{
				$objDelaRespuesta->respuesta="Solo admin";
			}
		}else
		{
			$response = $next($request, $response);
		}
	}else
	{
		
		$response = $next($request, $response);
	}

	if($objDelaRespuesta->respuesta!="")
	{
		$nueva=$response->withJson($objDelaRespuesta, 401);  
		return $nueva;
	}
	
	return $response;   
}
}
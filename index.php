<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Database\Capsule\Manager as Capsule;
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once './app/vendor/autoload.php';
require_once './app/models/AccesoDatos.php';
require_once './app/controllers/usuarioController.php';
require_once './app/controllers/criptomonedaController.php';
require_once './app/controllers/ventaCriptoController.php';
require_once './app/controllers/manejoArchivos.php';
//require_once './apis/manejoArchivos.php';
//require_once './apis/informacion.php';
require './app/mw/MWparaAutentificar.php';


$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$app = new \Slim\App(["settings" => $config]);


$container=$app->getContainer();

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'segundoparcial',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();




/*CON ESTO LOGEO PARA OBTENER EL JWT*/ 
$app->group('/login',function() {

  $this->post('/', \MWparaAutentificar::class . ':VerificarLogin');

});
/*LLAMADA A METODOS DE INSTANCIA DE UNA CLASE*/
$app->group('/usuario', function () {

  $this->get('/', \usuarioController::class . ':TraerTodos');
 
  $this->get('/{id}', \usuarioController::class . ':TraerUno');

  $this->post('/', \usuarioController::class . ':CargarUno');

  $this->delete('/{id}', \usuarioController::class . ':BorrarUno');

  $this->put('/', \usuarioController::class . ':ModificarUno');
     
})->add(\MWparaAutentificar::class . ':VerificarUsuario');

$app->group('/criptomoneda', function () {

    $this->get('/', \criptomonedaController::class . ':TraerTodos');
   
    $this->get('/{id}', \criptomonedaController::class . ':TraerUno');
    
    $this->get('/nacionalidad/{nacionalidad}', \criptomonedaController::class . ':TraerTodasPorNacionalidad');

    $this->post('/', \criptomonedaController::class . ':CargarUno');
  
    $this->delete('/{id}', \criptomonedaController::class . ':BorrarUno');
  
    $this->put('/', \criptomonedaController::class . ':ModificarUno');
       
  })->add(\MWparaAutentificar::class . ':VerificarUsuario');

$app->group('/venta', function () {

$this->get('/', \ventaCriptoController::class . ':TraerTodos');

$this->get('/{id}', \ventaCriptoController::class . ':TraerUno');

$this->get('/nacionalidad/{nacionalidad}', \ventaCriptoController::class . ':TraerTodosNacionalidad');

$this->get('/nombre/{nombre}', \ventaCriptoController::class . ':TraerTodosNombres');

$this->post('/', \ventaCriptoController::class . ':CargarUno');

$this->delete('/{id}', \ventaCriptoController::class . ':BorrarUno');

$this->put('/', \ventaCriptoController::class . ':ModificarUno');
    
})->add(\MWparaAutentificar::class . ':VerificarUsuario');

$app->group('/archivos', function () {

    $this->get('/', \manejoArchivos::class . ':GuardarPDF');
        
    })->add(\MWparaAutentificar::class . ':VerificarUsuario');

$app->run();

?>
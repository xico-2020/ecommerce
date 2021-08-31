<?php 

session_start();
require_once("vendor/autoload.php");   // criado autoload.php com o Composer

use \Slim\Slim;

/*  os use abaixo deixaram de ser necessários pois foram substituidos pelo require_once apos separacao em ficheiros por tipo de chamada.
use Hcode\PageAdmin;
use Hcode\Model\User;
use Hcode\Model\Category;
*/

$app = new Slim();  // Para usar as rotas

$app->config('debug', true);   // configuracao do debug para mostrar os erros

require_once("site.php");
require_once("functions.php");
require_once("admin.php");
require_once("admin-users.php");
require_once("admin-categories.php");
require_once("admin-products.php");




$app->run();   // mandar executar



?>
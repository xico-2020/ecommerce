<?php 

require_once("vendor/autoload.php");   // criado autoload.php com o Composer

$app = new \Slim\Slim();

$app->config('debug', true);   // configuracao do debug para mostrar os erros

$app->get('/', function() {   // criacao de rota "/"
    
	//echo "OK";  // mostra OK quando carrega a pagina
	$sql = new Hcode\DB\Sql();
	$results = $sql->select("SELECT * FROM tb_users");
	echo json_encode($results);

});

$app->run();   // mandar executar

 ?>
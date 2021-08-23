<?php 

require_once("vendor/autoload.php");   // criado autoload.php com o Composer

use \Slim\Slim;

use Hcode\Page;    // usar a classe Page

use Hcode\PageAdmin;

$app = new Slim();  // Para usar as rotas

$app->config('debug', true);   // configuracao do debug para mostrar os erros

$app->get('/', function() {   // criacao de rota "/"

	$page = new Page();  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("index");   // Adiciona o <h1>
    
	//echo "OK";  // mostra OK quando carrega a pagina
	//$sql = new Hcode\DB\Sql();
	//$results = $sql->select("SELECT * FROM tb_users");
	//echo json_encode($results);

});


$app->get('/admin/', function() {   // criacao de rota "/admin". Eata rota é para a pagina de administracao.

	$page = new PageAdmin();  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("index");   // Adiciona o <h1>
    
});

$app->run();   // mandar executar

 ?>
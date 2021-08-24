<?php 

session_start();
require_once("vendor/autoload.php");   // criado autoload.php com o Composer

use \Slim\Slim;

use Hcode\Page;    // usar a classe Page

use Hcode\PageAdmin;

use Hcode\Model\User;


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

	User::verifyLogin();

	$page = new PageAdmin();  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("index");   // Adiciona o <h1>
    
});

$app->get('/admin/login', function() {   // criacao de rota "/admin/login". Eata rota é para a pagina de login. Como não tem header e footer, desabilito-os para nao serem chamados

	$page = new PageAdmin([
		"header=>false",
		"footer"=>false
	]);  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("login");   // Adiciona o <h1>
    
});

$app->post('/admin/login', function(){   // criada nova rota dado que no login.html a form action admin/login tem o metodo post. Crio a funcao.

	User::login($_POST["login"], $_POST["password"]);   // validar o login. Crio uma classe User e um metodo estatico login porque nao sabemmos ainda quem é o usuario, que recebe o post do login e password.

	header("Location: /admin");   // Se não der estoirar com alguma exception (tiver sucesso),  redireciono para a nossa home page de administracao.
	exit;  // para parar.
});

$app->get('/admin/logout', function() {

	User::logout();
	header("Location: /admin/login");
	exit;
});

$app->run();   // mandar executar

 ?>
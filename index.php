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

$app->get('/admin/login', function() {   // criacao de rota "/admin/login". Esta rota é para a pagina de login. Como não tem header e footer, desabilito-os para nao serem chamados

	$page = new PageAdmin([
		"header=>false",
		"footer"=>false
	]);  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("login");   // Adiciona o <h1>
    
});

$app->post('/admin/login', function(){   // criada nova rota dado que no login.html a form action admin/login tem o metodo post. Crio a funcao.

	User::login($_POST["login"], $_POST["password"]);   // validar o login. Crio uma classe User e um metodo estatico login porque nao sabemmos ainda quem é o usuario, que recebe o post do login e password.

	header("Location:/admin");   // Se não der estoirar com alguma exception (tiver sucesso),  redireciono para a nossa home page de administracao.
	exit;  // para parar.
});

$app->get('/admin/logout', function() {

	User::logout();
	header("Location:/admin/login");
	exit;
});

$app->get("/admin/users", function() {

	User::verifyLogin();

	$users = User::listAll();

	$page = new PageAdmin();  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("users", array(
		"users"=>$users
	));   // Adiciona o template "users" que está em views/admin.
});


$app->get("/admin/users/create", function() {

	User::verifyLogin();

	$page = new PageAdmin();  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("users-create");   // Adiciona o template "users" que está em views/admin.
});



$app->get("/admin/users/:iduser/delete", function($iduser) {  //ATENCAO. Por causa da interpretacao do SLIM tenho que colocar a rota mais comprida primeiro para a executar. Neste caso compara ":iduser/delete" com ":iduser" abaixo.

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location:/admin/users");

	exit;



});


$app->get("/admin/users/:iduser", function($iduser) {  //Rota para Update. Neste caso passa o ID para mostrar os campos preenchidos.

	User::verifyLogin();
	
	$user = new User();
	$user->get((int)$iduser);   // convertido para numerico.

	$page = new PageAdmin();  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("users-update", array(
		"user"=> $user->getValues()
	));
});


$app->post("/admin/users/create", function() {

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->setData($_POST);

	$user->save();

	header("Location:/admin/users");

	exit;

});


$app->post("/admin/users/:iduser", function($iduser) {

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location:/admin/users");

	exit;


});


$app->get("/admin/forgot", function() {

	$page = new PageAdmin([
		"header=>false",
		"footer"=>false
	]);  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("forgot");   // Adiciona o <h1>
});


$app->post("/admin/forgot", function() {

	$_POST["email"] ;            //email corresponde ao "name" de email no forgot.html (views/admin)

	$user = User::getForgot($_POST["email"]);

	header("Location:/admin/forgot/sent");

	exit;

});

$app->get("/admin/forgot/sent", function(){

	$page = new PageAdmin([
		"header=>false",
		"footer"=>false
	]);  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("forgot-sent");   // forgot-sent é um Template html e está em views/admin.


});


$app->get("/admin/forgot/reset", function() {

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header=>false",
		"footer"=>false
	]);  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));   // forgot-reset é um Template html e está em views/admin.
});


$app->post("/admin/forgot/reset", function() {


	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);
	
	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);
	
	$user->setPassword($password);

	$page = new PageAdmin([
		"header=>false",
		"footer"=>false
	]);  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("forgot-reset-success");   // sem array porque não é presiso passar nada. forgot-reset-sucess é um Template html e está em views/admin.

});



$app->run();   // mandar executar

 ?>
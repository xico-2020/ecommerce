<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app->get('/admin/', function() {   // criacao de rota "/admin". Eata rota é para a pagina de administracao.

	User::verifyLogin();  // Verifica o login aqui pois é a rota para onde é enviado (header("Location:/admin")) apos o post do admin/login, ou seja apos ter recebido os dados.

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

	header("Location:/admin");   // Se não estoirar com alguma exception (tiver sucesso),  redireciono para a nossa home page de administracao.
	exit;  // para parar.
});

$app->get('/admin/logout', function() {

	User::logout();
	header("Location:/admin/login");
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


$app->get("/admin/forgot/reset", function() {    // mostra o template para inserir a nova senha

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header=>false",
		"footer"=>false
	]);  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("forgot-reset", array(      // passar os dados para o templte.
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));   // forgot-reset é um Template html e está em views/admin.
});


$app->post("/admin/forgot/reset", function() {     // recebe e trata a senha introduzida


	$forgot = User::validForgotDecrypt($_POST["code"]);  // verificar de novo para impedir tentativa intrusao.

	User::setForgotUsed($forgot["idrecovery"]);   // metodo que informa que foi usada recuperacao.

	$user = new User();

	$user->get((int)$forgot["iduser"]);  // carrega o usuario passando um inteiro da variavel forgot posicao iduser
	
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



 ?>
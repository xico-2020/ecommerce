<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app->get("/admin/users", function() {     // lista todos os usuarios

	User::verifyLogin();

	$users = User::listAll();

	$page = new PageAdmin();  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("users", array(    // cria chave que passa uma chave "users" com o valor da variavel $users .
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
	$user->get((int)$iduser);   // chama o metodo get na classe User. iduser convertido para numerico.

	$page = new PageAdmin();  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("users-update", array(
		"user"=> $user->getValues()
	));
});


$app->post("/admin/users/create", function() {   // traz os valore inseridos no html create.

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;  //  Se o valor de inadmin tiver sido definido é 1 se nao é 0.

	$_POST["despassword"] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);  // encripta password.

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




 ?>
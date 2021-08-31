<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

$app->get("/admin/categories", function(){

	User::verifyLogin();

	$categories = Category::listAll();

	$page = new PageAdmin();  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("categories", [
		"categories"=>$categories
	]);
}); 


$app->get("/admin/categories/create", function(){

	User::verifyLogin();

	$page = new PageAdmin();  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("categories-create");
}); 

$app->post("/admin/categories/create", function(){

	User::verifyLogin();

	$category = new Category();
	$category->setData($_POST);    // Vai buscar os dados do array global $_POST
	$category->save();
	header("Location:/admin/categories");
	exit;
	
}); 

$app->get("/admin/categories/:idcategory/delete", function($idcategory){

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);    // Vai buscar os dados do array global $_POST
	$category->delete();
	header("Location:/admin/categories");
	exit;
	
}); 


$app->get("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);  // idcategory como vem da URL vem na forma de texto. Com o (int) converto em numerico.

	$page = new PageAdmin(); 

	$page->setTpl("categories-update", [
		"category"=>$category->getValues()   // category é o nome da variavel que aguarda no template.
	]);
	
}); 


$app->post("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);  // idcategory como vem da URL vem na forma de texto. Com o (int) converto em numerico.

	$category->setData($_POST);
	$category->save();

	header("Location:/admin/categories");
	exit;

}); 

$app->get("/categories/:idcategory", function($idcategory) {
	$category = new Category();
	$category->get((int)$idcategory);
	$page = new Page();  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("category", [
		"category"=>$category->getValues(),
		"products"=>[]
	]);  

});




 ?>
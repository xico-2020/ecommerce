<?php 

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;

$app->get('/', function() {   // criacao de rota "/"

	$products = Product::listAll();

	$page = new Page();  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("index", [
		"products"=>Product::checkList($products)
	]);   // Adiciona o <h1>
    
	//echo "OK";  // mostra OK quando carrega a pagina
	//$sql = new Hcode\DB\Sql();
	//$results = $sql->select("SELECT * FROM tb_users");
	//echo json_encode($results);

});


$app->get("/categories/:idcategory", function($idcategory) {

	$category = new Category();
	$category->get((int)$idcategory);
	$page = new Page();  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("category", [
		"category"=>$category->getValues(),
		"products"=>Product::checkList($category->getProducts())
	]);  

});




 ?>
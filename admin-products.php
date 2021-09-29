<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

$app->get("/admin/products", function(){

	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";  // Se existir passa o valor se não passa vazio.

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if ($search != '')
		{
			$pagination = Product::getPageSearch($search, $page);

		} else
			{
				$pagination = Product::getPage($page);
			}


	$pages = [];

	for ($x = 0; $x > $pagination['pages']; $x++)
		{
			array_push($pages, array(
            "href"=>"/admin/products?".http_build_query(array(
            "page"=>$x + 1,
            "search"=>$search
        	)),
	        "text"=>$x + 1,
	        "active"=>(($x + 1) == $page)
    ));
			/*
			array_push($pages, [
				'href'=>'/admin/categories?'.http_buil_query([
					'page'=>$x+1,
					'search'=>$search
				]),
				'text'=>$x+1
			]);
			*/
		}


	//$products = Product::listAll();

	$page = new pageAdmin();

	$page->setTpl("products", [
		"products"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages   // $pages é o array definido acima que contem todas as paginas.
	]);

});

$app->get("/admin/products/create", function(){

	User::verifyLogin();

	$page = new pageAdmin();

	$page->setTpl("products-create");

});


$app->post("/admin/products/create", function(){

	User::verifyLogin();

	$product = new Product();
	$product->setData($_POST);
	$product->save();

	if($_FILES["file"]["name"] !== "") $product->setPhoto($_FILES['file']);  // para guardar a foto.
	
	header("Location:/admin/products");
	exit;

});


$app->get("/admin/products/:idproduct", function($idproduct){

	User::verifyLogin();
	$product = new Product();
	$product->get((int)$idproduct);
	$page = new pageAdmin();


	$page->setTpl("products-update", [
		"product"=>$product->getValues()
	]);

});


$app->post("/admin/products/:idproduct", function($idproduct){

	User::verifyLogin();
	$product = new Product();
	$product->get((int)$idproduct);

	$product->setData($_POST);
	$product->save();

	if(file_exists($_FILES['file']['tmp_name']) || is_uploaded_file($_FILES['file']['tmp_name'])) 
	{
    	$product->setPhoto($_FILES["file"]);   // ´"file" vem de products-update.html na parte photo - name = "file".
	 }


	header("Location:/admin/products");
	exit;

});


$app->get("/admin/products/:idproduct/delete", function($idproduct){

	User::verifyLogin();
	$product = new Product();
	$product->get((int)$idproduct);

	$product->delete();

	header("Location:/admin/products");
	exit;

});



 ?>
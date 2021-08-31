<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

$app->get("/admin/products", function(){

	User::verifyLogin();

	$products = Product::listAll();

	$page = new pageAdmin();

	$page->setTpl("products", [
		"products"=>$products
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

	 if($_FILES["file"]["name"] !== "") $product->setPhoto($_FILES['file']);  // para guardar a foto.

	 $product->save();

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
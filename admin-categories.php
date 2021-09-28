<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;

$app->get("/admin/categories", function(){

	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";  // Se existir passa o valor se não passa vazio.

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if ($search != '')
		{
			$pagination = Category::getPageSearch($search, $page);

		} else
			{
				$pagination = Category::getPage($page);
			}


	$pages = [];

	for ($x = 0; $x > $pagination['pages']; $x++)
		{
			array_push($pages, [
				'href'=>'/admin/categories?'.http_buil_query([
					'page'=>$x+1,
					'search'=>$search
				]),
				'text'=>$x+1
			]);
		}


	//$categories = Category::listAll();

	$page = new PageAdmin();  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("categories", [
		"categories"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages   // $pages é o array definido acima que contem todas as paginas.
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


$app->get("/admin/categories/:idcategory/products", function($idcategory){

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);
	$page = new PageAdmin();  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("categories-products", [
		"category"=>$category->getValues(),
		"productsRelated"=>$category->getProducts(),  // nao ponho true pois já é padrão (definido na classe Category (related = true))
		"productsNotRelated"=>$category->getProducts(False)
	]);
});


$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);

	$product = new Product();
	$product->get((int)$idproduct);
	$category->addProduct($product);

	header("Location:/admin/categories/".$idcategory."/products");
	exit;
	
});



$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);

	$product = new Product();
	$product->get((int)$idproduct);
	$category->removeProduct($product);

	header("Location:/admin/categories/".$idcategory."/products");
	exit;
	
});






 ?>
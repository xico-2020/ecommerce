<?php 

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;

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

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;  // se trouxer num de pagina é esse numero senão é pag 1.

	$category = new Category();
	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);

	$pages = [];

	for ($i = 1; $i <= $pagination['pages']; $i++) {     // pages vem do return de category.php rota para paginacao
		array_push($pages, [  // a seguir carrrega os valores necessários para o loop de category.html
			"link"=>"/categories/".$category->getidcategory()."?page=".$i,  // envia para /categories/ o id da categoria  e concatena com a page (vem da linha $_GET acima) e com a variavel de incremento
			"page"=>$i    // só para mostrar o numero da pagina.
		]);
	}

	$page = new Page();  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("category", [
		"category"=>$category->getValues(),
		"products"=>$pagination["data"],
		"pages"=>$pages   // pages vem de category.html para passagem de valores e é definido acima.
	]);  

});


$app->get("/products/:desurl", function($desurl) {   

	$product = new Product();

	$product->getFromURL($desurl);

	$page = new Page();

	$page->setTpl("product-detail", [
		"product"=>$product->getValues(),
		"categories"=>$product->getCategories()
	]);
});


$app->get("/cart", function(){
	$cart = Cart::getFromSession();
	$page = new Page();
	$page->setTpl("cart");
});




 ?>
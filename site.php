<?php 

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;

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

	//var_dump($cart->getValues());  Para ver as informacoes retornadas do carrinho em $cart.
	//exit;

	$page->setTpl("cart", [
		"cart"=>$cart->getValues(),      // passa as informacoes do carrinho
		"products"=>$cart->getProducts(), // passa os produtos
		"error"=>Cart::getMsgError()  // passa a mensagem de erro se houver
	]);
});



$app->get("/cart/:idproduct/add", function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();     // método que tem a inteligencia de recuperar o carrinho da sessão ou gerar um novo.

	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;  // Se a variavel $qtd vier diferente de 1 é esse valor senao é 1.

	for ($i = 0; $i < $qtd; $i++) {

		$cart->addProduct($product);  // $cart (carrinho) recebe uma instancia de Produtos.
	}

	//Location:/cart#table-cart
	//Location:/cart	

	header("Location:/cart");    // depois de adicionar redireciona para o separador /cart para ver como ficou o carrinho.

	exit;


});


$app->get("/cart/:idproduct/minus", function($idproduct){   // minus porque só quero remover 1

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();     // recuperar o carrinho da sessão.

	$cart->removeProduct($product);   // como por padrão a remocao de todos é false não preciso de passar essa informacao (parametro).

	header("Location:/cart");    // depois de adicionar redireciona para o separador /cart para ver como ficou o carrinho.

	exit;


});


$app->get("/cart/:idproduct/remove", function($idproduct){   // minus porque só quero remover 1

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();     // recuperar o carrinho da sessão.

	$cart->removeProduct($product, true);  // Para todos tenho que passar mais uma variavel com true pois por padrão a remocao para todos é false

	header("Location:/cart");    // depois de adicionar redireciona para o separador /cart para ver como ficou o carrinho.

	exit;

});


$app->post("/cart/freight", function() {

	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']);  // zipcode é o name no cart.html onde é chamado o CEP

	header("Location:/cart");    // depois de adicionar redireciona para o separador /cart para ver como ficou o carrinho.

	exit;

});


$app->get("/checkout", function() {

	User::verifyLogin(false);    // passa false para o parametro inadmin que por defeito é true. Esta rota só pode continuar se o utilizador estiver logado.
	$cart = Cart::getFromSession();
	$address = new Address();
	$page = new Page();

	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues()
	]);
});



$app->get("/login", function() {

	$page = new Page();
	$page->setTpl("login", [
		'error'=>User::getError()    // passa o erro para o template
	]);
});



$app->post("/login", function() {

	try {

		User::login($_POST['login'], $_POST['password']);  // login e password são o name no input em login.html

	} catch(Exception $e) {

		User::setError($e->getMessage());
	}


	header("Location:/checkout");

	exit;

});


$app->get("/logout", function() {

	User::logout();

	header("Location:/login");

	exit;

});


 ?>
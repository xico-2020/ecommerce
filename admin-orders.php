<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;


// As rotas maiores ficam antes para evitar que umas sobreescrevam outras.


$app->get("/admin/orders/:idorder/status", function($idorder){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);  // verifica se o pedido existe na BD

	$page = new PageAdmin();

	$page->setTpl("order-status", [
		'order'=>$order->getValues(),           // Campos passados paro o order-status.html pois são usados lá.
		'status'=>OrderStatus::listAll(),
		'msgSuccess'=>Order::getSuccess(),
		'msgError'=>Order::getError()
	]);

});


$app->post("/admin/orders/:idorder/status", function($idorder){

	User::verifyLogin();

	if (!isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0)
		{
			Order::setError("Indique o status atual!");
			header("Location:/admin/orders/".$idorder."/status");
			exit;
		}

	$order = new Order();

	$order->get((int)$idorder);  // verifica se o pedido existe na BD

	$order->setidstatus((int)$_POST['idstatus']);

	$order->save();

	Order::setSuccess("Status atualizado!");

	header("Location:/admin/orders/".$idorder."/status");

	exit;

});


$app->get("/admin/orders/:idorder/delete", function($idorder){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);  // verifica se o pedido existe na BD

	$order->delete();

	header("Location:/admin/orders");

	exit;


});


$app->get("/admin/orders/:idorder", function($idorder){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);  // verifica se o pedido existe na BD

	$cart = $order->getCart();  // Como o idcart está na consulta do pedido (order) sirvo-me dele para consultar o carrinho. Crio método na classe Order para isso.

	$page = new PageAdmin();

	$page->setTpl("order", [
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts()
	]);


});



$app->get("/admin/orders", function() {

	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";  // Se existir passa o valor se não passa vazio.

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if ($search != '')
		{
			$pagination = Order::getPageSearch($search, $page);

		} else
			{
				$pagination = Order::getPage($page);
			}


	$pages = [];

	for ($x = 0; $x > $pagination['pages']; $x++)
		{
			array_push($pages, array(
            "href"=>"/admin/orders?".http_build_query(array(
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



	$page = new PageAdmin();

	$page->setTpl("orders", [
		"orders"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages   // $pages é o array definido acima que contem todas as paginas.
	]);


});




 ?>
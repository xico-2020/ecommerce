<?php 

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;

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

$app->get('/produtosinicio', function() {   // criacao de rota "/"

	$products = Product::listAll();

	$page = new Page();  // criar $page que recebe o construtor vazio. Chama o construct e adiciona o header no ecran.

	$page->setTpl("produtos-inicio", [
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

	$address = new Address();
	$cart = Cart::getFromSession();

	if (!isset($_GET['zipcode']))
		{
			$_GET['zipcode'] = $cart->getdeszipcode();
		}

	if (isset($_GET['zipcode']))
		{
			$address->loadFromCEP($_GET['zipcode']);

			$cart->setdeszipcode($_GET['zipcode']);

			$cart->save();

			$cart->getCalculateTotal();
		}
	

	if (!$address->getdesaddress()) $address->setdesaddress('');
	if (!$address->getdesnumber()) $address->setdesnumber('');
	if (!$address->getdescomplement()) $address->setdescomplement('');
	if (!$address->getdesdistrict()) $address->setdesdistrict('');
	if (!$address->getdescity()) $address->setdescity('');
	if (!$address->getdesstate()) $address->setdesstate('');
	if (!$address->getdescountry()) $address->setdescountry('');
	if (!$address->getdeszipcode()) $address->setdeszipcode('');	 

	
	$page = new Page();

	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Address::getMsgError()
	]);
});


$app->post("/checkout", function() {

	User::verifyLogin(false);    // passa false para o parametro inadmin que por defeito é true. Esta rota só pode continuar se o utilizador estiver logado.


	if (!isset($_POST['zipcode']) || $_POST['zipcode'] === '')
		{
			Address::setMsgError("Informe o CEP !");
			header("Location:/checkout");
			exit;
		}

	if (!isset($_POST['desaddress']) || $_POST['desaddress'] === '')
		{
			Address::setMsgError("Informe o endereço!");
			header("Location:/checkout");
			exit;
		}

	if (!isset($_POST['desnumber']) || $_POST['desnumber'] === '')
		{
			Address::setMsgError("Informe o numero!");
			header("Location:/checkout");
			exit;
		}

	if (!isset($_POST['desdistrict']) || $_POST['desdistrict'] === '')
		{
			Address::setMsgError("Informe o bairro!");
			header("Location:/checkout");
			exit;
		}

	if (!isset($_POST['descity']) || $_POST['descity'] === '')
		{
			Address::setMsgError("Informe a cidade!");
			header("Location:/checkout");
			exit;
		}

	if (!isset($_POST['desstate']) || $_POST['desstate'] === '')
		{
			Address::setMsgError("Informe o estado!");
			header("Location:/checkout");
			exit;
		}

	if (!isset($_POST['descountry']) || $_POST['descountry'] === '')
		{
			Address::setMsgError("Informe o país!");
			header("Location:/checkout");
			exit;
		}

	
	$user = User::getFromSession();

	$address = new Address();

	$_POST['deszipcode'] = $_POST['zipcode'];  // No checkout.html está zipcode em vez de deszipcode
	$_POST['idperson'] = $user->getidperson();

	$address->setData($_POST);

	$address->save();

	$cart = CART::getFromSession();

	$cart->getCalculateTotal();

	$order = new Order();

	$order->setData([
		'idcart'=>$cart->getidcart(),
		'idaddress'=>$address->getidaddress(),
		'iduser'=>$user->getiduser(),
		'idstatus'=>OrderStatus::EM_ABERTO,
		'vltotal'=>$cart->getvltotal()
		
	]);

	$order->save();

	switch ((int)$_POST['payment-method']) {

		case 1:
		//header("Location:/checkout");
			//exit;
		header("Location:/order/".$order->getidorder()."/pagseguro");
		break;

		case 2:
		header("Location:/order/".$order->getidorder()."/paypal");
		break;

		case 3:
		header("Location:/order/".$order->getidorder()."/boleto");
		break;

	}

	$cart->removeSession();  // adicionado para que o carrinho não apareca quando se loga outro utilizador.

	//header("Location:/order/".$order->getidorder());

	exit;
	
});

$app->get("/order/:idorder/pagseguro", function($idorder) {

	/*
	header("Location:/checkout");

	exit;
	*/

	///*

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$cart = $order->getCart();

	$page = new Page([
		'header'=>false,
		'footer'=>false
	]);

	
	$page->setTpl("payment-pagseguro", [
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'phone'=>[
			'areaCode'=>substr($order->getnrphone(), 0, 2),
			'number'=>substr($order->getnrphone(), 2, strlen($order->getnrphone()))
		]
	]);
	
	//*/
	

});


$app->get("/order/:idorder/paypal", function($idorder) {

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$cart = $order->getCart();

	$page = new Page([
		'header'=>false,
		'footer'=>false
	]);

	$page->setTpl("payment-paypal", [
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts()
	]);


});



$app->get("/login", function() {

	$page = new Page();
	$page->setTpl("login", [
		'error'=>User::getError(),    // passa o erro para o template
		'errorRegister'=>User::getErrorRegister(),
		'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>'']
	]);
});



$app->post("/login", function() {

	try {

		User::login($_POST['login'], $_POST['password']);  // login e password são o name no input em login.html

	} catch(Exception $e) {

		User::setError($e->getMessage());
	}


	//header("Location:/profile");
	header("Location:/");

	exit;

});


$app->get("/logout", function() {

	User::logout();

	Cart::removeFromSession();  // Estas duas linhas servem para limpar o carrinho da sessão quando o utilizador faz logout.
	session_regenerate_id();

	header("Location:/login");

	exit;

});


$app->post("/register", function() {

	$_SESSION["registerValues"] = $_POST;  // Criar sessao para guardar os valores dos campos digitados para nao serem perdidos em caso de erro de digitacao num deles (na validacao abaixo). Guarda por isso o array $_POST que é onde estao os valores.

	if (!isset($_POST['name']) || $_POST['name'] == "") 
		{
			User::setErrorRegister("Preencha o seu nome.");
			header("Location:/login");
			exit;
		}


	if (!isset($_POST['email']) || $_POST['email'] == "") 
		{
			User::setErrorRegister("Preencha o seu E-mail.");
			header("Location:/login");
			exit;
		}


	
	if (!isset($_POST['phone']) || $_POST['phone'] == "") 
		{
			User::setErrorRegister("Preencha o seu telefone.");
			header("Location:/login");
			exit;
		}
	

	if (User::checkLoginExist($_POST['email']) === true)
		{
			User::setErrorRegister("Este endereco de e-mail já está a ser usado por outro utilizador!");
			header("Location:/login");
			exit;
		}


	if (!isset($_POST['password']) || $_POST['password'] == "") 
		{
			User::setErrorRegister("Preencha a sua senha.");
			header("Location:/login");
			exit;
		}

	$pwdverify = User::checkPassword($_POST['password']);

	if ($pwdverify === false)
		{
			User::setErrorRegister("A senha deve ter pelo menos 4 carateres, letras maiúsculas, minúsculas, números e caracteres especiais!");
			header("Location:/login");
			exit;
		}


	$user = new User();

	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphone'=>$_POST['phone']
	]);

	$user->save();

	User::login($_POST['email'], $_POST['password']);  // como estou a fazer o registo faco logo o login.

	header("Location:/checkout");

	exit;
});


// -------------- Forgot passoword ----



$app->get("/forgot", function() {

	$page = new Page();  // criar $page que recebe o construtor vazio. 

	$page->setTpl("forgot");

	/*
	$page->setTpl("forgot", [
	'ErrorRegister'=>User::getErrorRegister()
	]); 	
	*/

});


$app->post("/forgot", function() {
	
	/*
	$_POST["email"] ;
	
	if (!isset($_POST['email']) || $_POST['email'] === "")
		{
			User::setErrorRegister("Digite um endereço de e-mail válido!");
			header("Location:/forgot");
			exit;
		} 


	if (User::checkLoginExist($_POST['email']) === false)
		{
			User::setErrorRegister("E-mail inexistente na Base de Dados!");
			header("Location:/forgot");
			exit;
					
		} 


	$user = User::getForgot($_POST["email"], false);

	header("Location:/forgot/sent");

	exit;
		
	*/	

	$_POST["email"] ;           //email corresponde ao "name" de email no forgot.html (views)

	
	$user = User::getForgot($_POST["email"], false);

	header("Location:/forgot/sent");

	exit;
	
	
});

$app->get("/forgot/sent", function(){

	$page = new Page();  // criar $page que recebe o construtor vazio. 

	$page->setTpl("forgot-sent");   // forgot-sent é um Template html e está em views.


});


$app->get("/forgot/reset", function() {    // mostra o template para inserir a nova senha

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new Page();  // criar $page que recebe o construtor vazio. 

	$page->setTpl("forgot-reset", array(      // passar os dados para o templte.
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));   // forgot-reset é um Template html e está em views.
});


$app->post("/forgot/reset", function() {     // recebe e trata a senha introduzida


	$forgot = User::validForgotDecrypt($_POST["code"]);  // verificar de novo para impedir tentativa intrusao.

	User::setForgotUsed($forgot["idrecovery"]);   // metodo que informa que foi usada recuperacao.

	$user = new User();

	$user->get((int)$forgot["iduser"]);  // carrega o usuario passando um inteiro da variavel forgot posicao iduser
	
	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);
	
	$user->setPassword($password);

	$page = new Page();  // criar $page que recebe o construtor vazio. 

	$page->setTpl("forgot-reset-success");   // sem array porque não é presiso passar nada. forgot-reset-sucess é um Template html e está em views.

});

// -------------- Fim Forgot passoword ----


$app->get("/profile", function(){

	User::verifyLogin(false);  // não é administrador

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile", [
		'user'=>$user->getValues(),
		'profileMsg'=>User::getSuccess(),
		'profileError'=>User::getError()
	]);


});


$app->post("/profile", function(){

	User::verifyLogin(false);

	if (!isset($_POST['desperson']) || $_POST['desperson'] === '') 
		{
			User::setError("Preencha o seu nome!");
			header("Location:/profile");
			exit;
		}

	if (!isset($_POST['desemail']) || $_POST['desemail'] === '') 
		{
			User::setError("Preencha o seu e-mail!");
			header("Location:/profile");
			exit;
		}


	$user = User::getFromSession();  // aproveita os dados do utilizador e altera os dados alterados.

	if ($_POST['desemail'] !== $user->getdesemail())  // verifica se houve alteração de email e se o novo já existe
		{
			if (User::checkLoginExist($_POST['desemail']) === true)
			{
				User::setError("Este endereço de e-mail já existe!");
				header("Location:/profile");
				exit;
			}
		}

	
	$_POST['iduser'] = $user->getiduser();   // Le o iduser

	$_POST['inadmin'] = $user->getinadmin();  // Vai ler o valor do inadmin existente e sobreescreve para evitar alterações. Ignora o que está a ser enviado e guarda o que está na Base Dados. Pode um utilizador mal intencionado, no browser via developer tools descobrir os parametros passados via POST e alterar algum.

	$_POST['despassword'] = $user->getdespassword();

	$_POST['deslogin'] = $_POST['desemail'];   // O login é o email.

	$user->setData($_POST);

	$user->updateParcial();

	$_SESSION[User::SESSION] = $user->getValues();

	User::setSuccess("Dados alterados com sucesso!");

	header("Location:/profile");

	exit;

});


$app->get("/order/:idorder/boleto", function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$page = new Page();

	$page->setTpl("payment",[
		'order'=>$order->getValues()
	]);
});


$app->get("/boleto/:idorder", function($idorder){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	// DADOS DO BOLETO PARA O SEU CLIENTE
	$dias_de_prazo_para_pagamento = 10;
	$taxa_boleto = 5.00;
	$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
	$valor_cobrado = $order->getvltotal(); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
	$valor_cobrado = str_replace(",", ".",$valor_cobrado);
	$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

	$dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
	$dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
	$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
	$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
	$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
	$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

	// DADOS DO SEU CLIENTE
	$dadosboleto["sacado"] = $order->getdesperson();
	$dadosboleto["endereco1"] = $order->getdesaddress() . " " . $order->getdesdistrict(); 
	$dadosboleto["endereco2"] = $order->getdescity() . " - " . $order->getdesstate() . " - " . $order->getdescountry() . " -  CEP: " . $order->getdeszipcode();

	// INFORMACOES PARA O CLIENTE
	$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Hcode E-commerce";
	$dadosboleto["demonstrativo2"] = "Taxa bancária - € 0,00";
	$dadosboleto["demonstrativo3"] = "";
	$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
	$dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
	$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@hcode.com.br";
	$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

	// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
	$dadosboleto["quantidade"] = "";
	$dadosboleto["valor_unitario"] = "";
	$dadosboleto["aceite"] = "";		
	$dadosboleto["especie"] = "€";
	$dadosboleto["especie_doc"] = "";


	// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


	// DADOS DA SUA CONTA - ITAÚ
	$dadosboleto["agencia"] = "1690"; // Num da agencia, sem digito
	$dadosboleto["conta"] = "48781";	// Num da conta, sem digito
	$dadosboleto["conta_dv"] = "2"; 	// Digito do Num da conta

	// DADOS PERSONALIZADOS - ITAÚ
	$dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

	// SEUS DADOS
	$dadosboleto["identificacao"] = "TRYSOFTWARE";
	$dadosboleto["cpf_cnpj"] = "2410-111 LEIRIA";
	$dadosboleto["endereco"] = "Rua da Experiencia 69";
	$dadosboleto["cidade_uf"] = "Leiria";
	$dadosboleto["cedente"] = "TRYSOFTWARE SA";

	// NÃO ALTERAR!
	$path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "boletophp" . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR;

	require_once($path . "funcoes_itau.php");
	require_once($path . "layout_itau.php");

});


$app->get("/profile/orders", function(){

	User::verifyLogin();

	$user = User::getFromSession();  // traz o User para a rota.

	$page = new Page();

	$page->setTpl("profile-orders", [
	'orders'=>$user->getOrders()
	]);


});


$app->get("/profile/orders/:idorder", function($idorder){

	User::verifyLogin();

	$order = new Order();

	$order->get((int)$idorder);

	$cart = new Cart();

	$cart->get((int)$order->getidcart());  // carrega o carrinho da sessão (do pedido). para carregar nos detalhes

	$cart->getCalculateTotal();  // para mostrar os valores nos detalhes (profile-orders-detail.html).

	$page = new Page();

	$page->setTpl("profile-orders-detail", [
	'order'=>$order->getValues(),
	'cart'=>$cart->getValues(),
	'products'=>$cart->getProducts()
	]);

});


$app->get("/profile/change-password", function(){

	User::verifyLogin(false);

	$page = new Page();

	$page->setTpl("profile-change-password", [
		'changePassError'=>User::getError(),
		'changePassSuccess'=>User::getSuccess()
	]);


});


$app->post("/profile/change-password", function(){

	User::verifyLogin(false);

	$user = User::getFromSession();

	if (!isset($_POST['current_pass']) || $_POST['current_pass'] === '')
		{
			User::setError("Digite a senha atual!");
			header("Location:/profile/change-password");
			exit;
		}

	if(!password_verify($_POST['current_pass'], $user->getdespassword()))  // Compara a pwd nova com a da BD (com Hash).
		{
		User::setError("Senha atual inválida!");
		header("Location:/profile/change-password");
		exit;
		}
	

	if (!isset($_POST['new_pass']) || $_POST['new_pass'] === '')
		{
			User::setError("Digite a nova senha!");
			header("Location:/profile/change-password");
			exit;
		}

	$pwdverify = User::checkPassword($_POST['new_pass']);

	if ($pwdverify === false)
		{
			User::setError("A senha deve ter pelo menos 4 carateres, letras maiúsculas, minúsculas, números e caracteres especiais!");
			header("Location:/profile/change-password");
			exit;
		}

	if (!isset($_POST['new_pass_confirm']) || $_POST['new_pass_confirm'] === '')
		{
			User::setError("Confirme a nova senha!");
			header("Location:/profile/change-password");
			exit;
		}

	
	if ($_POST['new_pass_confirm'] != $_POST['new_pass'])
		{
			User::setError("Nova senha e confirmação devem ser iguais!");
			header("Location:/profile/change-password");
			exit;
		}


	if ($_POST['current_pass'] === $_POST['new_pass'])   // comparação em texto simples
		{
			User::setError("Nova senha tem que ser diferente da atual!");
			header("Location:/profile/change-password");
			exit;
		}

	/*
	$user = User::getFromSession();

	if(!password_verify($_POST['current_pass'], $user->getdespassword()))  // Compara a pwd nova com a da BD (com Hash).
	{
		User::setError("Senha atual inválida!");
		header("Location:/profile/change-password");
		exit;
	}
	*/

	$user->setdespassword($_POST['new_pass']);

	$user->update();

	User::setSuccess("Senha alterada com sucesso!");

	//$user->getValues();

	header("Location:/profile/change-password");
	exit;

});





 ?>
<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;

class Cart extends Model 
{
	const SESSION = "Cart";

	public static function getFromSession()
	{
		$cart = new Cart();

		if (isset($_SESSION['Cart::SESSION']) && (int)$_SESSION['Cart::SESSION["idcart"]'] > 0) {  // verifica se existe sessao e o ID for > 0, significa que o carrinho já está na BD e está na sessão.

			$cart->get((int)$_SESSION[Cart::SESSION]["idcart"]);   // Carrega o carrinho

		} else {
			$cart->getFromSessionID();  // ver se recupera o carrinho através da sessionID

			if (!(int)$cart->getidcart() > 0) {    // se nao consegiu carregar o carrinho cria um carrinho novo.
				$data =[
					"dessessionid"=>session_id()
				];


				if (User::checkLogin(false)) {  // o padrão de login é true mas como estou no carrinho e não na administracão é false.

					$user = User::getFromSession();

					$data["iduser"] = $user->getiduser();

				}

				$cart->setData($data);

				$cart->save();

				$cart->setToSession();
			}
		}

		return $cart;
	}


	public function setToSession()  // método não estático porque vai ser usada a variavel $this.
	{

		$_SESSION[Cart::SESSION] = $this->getValues();  // coloca o carrinho na sessão.

	}



	public function getFromSessionID()   // não recebe nenhum parametro pois vai buscar o sessionID via funcão do PHP (session_id)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
			":dessessionid"=>session_id()
		]);

		if (count($results) > 0) {

			$this->setData($results[0]);  // coloca no objeto os dados retornados
		}

	}
	


	public function get(int $idcart)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
			":idcart"=>$idcart
		]);

		if (count($results) > 0) {

			$this->setData($results[0]);  // coloca no objeto os dados retornados
		}

		
	}

	public function Save()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
			":idcart"=>$this->getidcart(),
			":dessessionid"=>$this->getdessessionid(),
			":iduser"=>$this->getiduser(),
			":deszipcode"=>$this->getdeszipcode(),
			":vlfreight"=>$this->getvlfreight(),
			":nrdays"=>$this->getnrdays()
		]);

		$this->setData($results[0]);
	}


	public function addProduct(Product $product)
	{
		$sql = new Sql();

		$sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES(:idcart, :idproduct)", [
			":idcart"=>$this->getidcart(),
			":idproduct"=>$product->getidproduct()

		]);

	}

	public function removeProduct(Product $product, $all = false) // Passa mais a variavel $all pois pode querer remover todos os produtos de uma vez.
	{

		$sql = new Sql();

		{
			if ($all)
			{
				$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", [
					":idcart"=>$this->getidcart(),
					":idproduct"=>$product->getidproduct()
				]);

			} else
				{
					$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [    // Como aqui $all é falso, remove apenas 1.
					":idcart"=>$this->getidcart(),
					":idproduct"=>$product->getidproduct()
				]);
				}
		}

	}


	public function getProducts()
	{
		$sql = new Sql();

		$rows = $sql->select("
			SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal
			FROM tb_cartsproducts a 
			INNER JOIN tb_products b ON a.idproduct = b.idproduct
			WHERE a.idcart = :idcart AND a.dtremoved IS NULL 
			GROUP BY b.idproduct, b.desproduct , b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
			ORDER BY b.desproduct
		", [
			":idcart"=>$this->getidcart()
		]);

		return Product::checkList($rows);  // Para tratar as fotos chamo o método estático de Product checkList. 

	}
	

}


 ?>
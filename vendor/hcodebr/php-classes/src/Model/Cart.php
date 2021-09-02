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




	

}


 ?>
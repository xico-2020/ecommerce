<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model 
{

	const SESSION = "User";

	public static function login($login, $password)    // Método que verifica se o que foi digitado existe na BD.
	{

		$sql = new Sql(); // para acessar a BD instancio a classe Sql.

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));

		if (count($results) === 0)
		{
			throw new \Exception("Usuário inixistente ou senha inválida.");  // \Exception porqu está no namespace principal e nao no Hcode.
		}

		$data = $results[0];  // os dados do usuario é igual ao $results na posicao 0 (primeiro registo encontrado).

		if (password_verify($password, $data["despassword"]) === true)
		{
			$user = new User();  // Aqui se tudo estiver certo estamos a criar uma instancia da propria classe.

			$user->setData($data);
			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		} else{
			throw new \Exception("Usuário inixistente ou senha inválida.");
		}
	}

	public static function verifyLogin($inadmin = true)
	{
		if (
			!isset($_SESSION[User::SESSION])   // Se nao existir
			||
			!$_SESSION[User::SESSION]  // Se for vazia
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0  // se o Id usuario não for > 0
			||
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin  // Se pode acessar como administracao.
		) {
			header("Location: /admin/login");
			exit;
		}
	}


	public static function logout() 
	{
		$_SESSION[User::SESSION] = NULL;
	}


}



 ?>
<?php 

//namespace Hcode;
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model 
{

	const SESSION = "User";

	const SECRET = "HcodePhp7_Secret";

	const ERROR = "UserError";

	const ERROR_REGISTER = "UserErrorRegister";

	public static function getFromSession()
	{
		$user = new User();

		if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]["iduser"] > 0) {

			$user->setData($_SESSION[User::SESSION]);
		}

		return $user;
	}

	public static function checkLogin($inadmin = true)
	{
		if (
			!isset($_SESSION[User::SESSION])   // Se nao existir
			||
			!$_SESSION[User::SESSION]  // Se for vazia
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0  // se o Id usuario não for > 0 (for vazio)
		) {
			// não está logado
			return false;

		} else {

			if($inadmin === true && (bool)$_SESSION[User::SESSION]["inadmin"] === true) {  // está logado e é administrador

				return true;

			} else 
				if ($inadmin === false) {      // está logado mas não é administrador.

					return true;

				} else {

					return false;

					}

		}
	}

	public static function login($login, $password)    // Método que verifica se o que foi digitado existe na BD.
	{

		$sql = new Sql(); // para acessar a BD instancio a classe Sql.

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE  a.deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));

		if (count($results) === 0)
		{
			return false;
			throw new \Exception("Usuário inexistente ou senha inválida.");  // \Exception porque está no namespace principal e nao no Hcode.
		}

		$data = $results[0];  // os dados do usuario é igual ao $results na posicao 0 (primeiro registo encontrado).

		if (password_verify($password, $data["despassword"]) === true)  // $password foi a inserida, $data[despassword] é a password na BD que está encriptada. A funcao password_verify desencripta e compara texto.
		{
			$user = new User();  // Aqui se tudo estiver certo estamos a criar uma instancia da propria classe.

			// $data['desperson'] = utf8_encode($data['desperson']);

			$user->setData($data);  // ver classe Model

			$_SESSION[User::SESSION] = $user->getValues();  // carrega os dados do usuario como array. Vai buscar ao objeto.

			return $user;

		} else{
			throw new \Exception("Utilizador inexistente, sem permissões ou senha inválida!");
		}
	}

	public static function verifyLogin($inadmin = true)
	{
		if (!User::checkLogin($inadmin))
		{
			if ($inadmin)
			{
				header("Location:/admin/login");
			} else
				{
					header("Location:/login");
				}

			exit;
		}
			
		 

		
	}


	public static function logout() 
	{
		$_SESSION[User::SESSION] = NULL;
	}


	public static function listAll()
	{
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");

	}


	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>$this->getdesperson(),    // :desperson - Associa com a chave. O setData em Model descodifica os gets...
			//":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		)); // chamada de procedure (ver mySql Workbench).  Vai buscar os dados e traz de volta.

	
		$this->setData($results[0]);

	}

	
	public function get($iduser) {
		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			":iduser"=>$iduser    //  Passa parametro via array
		));
 
		$data = $results[0];

		//$data['desperson'] = utf8_encode($data['desperson']);
 
		$this->setData($data);
	}


	public function update()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"=>$this->getiduser(),
			":desperson"=>$this->getdesperson(),
			//":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		)); // chamada de procedure (ver mySql Workbench).

	
		$this->setData($results[0]);

	}


	public function delete()
	{

		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()

		//$results = $sql->select("CALL sp_users_delete(:iduser)", array(
		//	":iduser"=>$this->getiduser()
			
		)); // chamada de procedure (ver mySql Workbench).


	}


	public static function getForgot($email, $inadmin = true)
	{

		$sql = new Sql();

		$results = $sql->select("
			SELECT * 
			FROM tb_persons a 
			INNER JOIN tb_users b USING(idperson) 
			WHERE a.desemail = :email;
			", array(
				":email"=>$email
		));

		if (count($results) === 0)
		{
			throw new \Exception("Nao foi possivel recuperar a senha");
		}
		else
		{
			$data = $results[0];
			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"=>$data["iduser"], 
				":desip"=>$_SERVER["REMOTE_ADDR"]
			));

			if (count($results2) === 0)
			{
			throw new \Exception("Nao foi possivel recuperar a senha");
			}
			else
			{
				$dataRecovery = $results2[0];

				$iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
             	$code = openssl_encrypt($dataRecovery['idrecovery'], 'aes-256-cbc', User::SECRET, 0, $iv);    // SECRET é uma constante criada no inicio da classe.
             	$result = base64_encode($iv.$code);


             	if ($inadmin === true) {
                	 $link = "http://www.fpauloecommerce.com.pt/admin/forgot/reset?code=$result";
             	} 	else {
                 	$link = "http://www.fpauloecommerce.com.pt/admin/forgot/reset?code=$code";
             	} 

				//$link = "http://www.fpauloecommerce.com.pt/admin/forgot/reset?code=$code";

				$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir senha Hcode Store", "forgot", 
					array(
					"name"=>$data["desperson"],   // name e link sao referencias no forgot.html
					"link"=>$link
				));

				$mailer->send();

				return $data;  // retorna os dados do usuario recuperado para o caso de vir a ser preciso.

			}
		}

	}

	
	
	public static function validForgotDecrypt($result)
 	{

	    $result = str_replace(' ', '+', $result);
	    
	    $result = base64_decode($result);
	    $code = mb_substr($result, openssl_cipher_iv_length('aes-256-cbc'), null, '8bit');
	    $iv = mb_substr($result, 0, openssl_cipher_iv_length('aes-256-cbc'), '8bit');
	    $idrecovery = openssl_decrypt($code, 'aes-256-cbc', User::SECRET, 0, $iv);

	    $sql = new Sql();
	    $results = $sql->select("
	        SELECT *
	        FROM tb_userspasswordsrecoveries a
	        INNER JOIN tb_users b USING(iduser)
	        INNER JOIN tb_persons c USING(idperson)
	        WHERE
	        a.idrecovery = :idrecovery
	        AND
	        a.dtrecovery IS NULL
	        AND
	        DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();   
	    ", array(
	        ":idrecovery"=>$idrecovery
	    ));

	    if (count($results) === 0)
	    {

	        throw new \Exception("Não foi possível recuperar a senha.");
	    }
	    else
	    {
	        return $results[0];   // devolve os dados do usuario.
	    }
 	}

	public static function setForgotUsed($idrecovery)
	{
		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
			":idrecovery"=>$idrecovery
		));
	}


	public function setPassword($password)
	{
		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>$password,
			":iduser"=>$this->getiduser()
		));
	}



	public static function getPasswordHash($password)
	{
		return password_hash($password, PASSWORD_DEFAULT, [
			'cost'=>12
		]);
	}




	public static function setError($msg)
	{
		$_SESSION[User::ERROR] = $msg;    //ERROR constante definida no principio da classe
	}

	public static function getError()
	{
		$msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : "";

		User::clearError();

		return $msg;
	}

	public static function clearError()
	{
		$_SESSION[User::ERROR] = NULL;
	}


	public static function setErrorRegister($msg)
	{
		$_SESSION[User::ERROR_REGISTER] = $msg;
	}

	public static function getErrorRegister()
	{
		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : "";

		User::clearErrorRegister();

		return $msg;
	}


	public static function clearErrorRegister()
	{
		$_SESSION[User::ERROR_REGISTER] = NULL;
	}


	public static function checkLoginExist($login)
	{
		$sql = new Sql();
 
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [ 
			":deslogin"=>$login
		]);

		return (count($results) > 0);
	}





}


 ?>
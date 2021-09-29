<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Product extends Model 
{
	public static function listAll()
	{
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

	}

	public static function checkList($list)  // funcao criada para passar o valor desphoto. Este campo era usado na criacao ou update de Produto em checkPhoto(). Como para o index.html na raiz nao está a passar pelo getValues o desphoto. O listAll só traz dados da BD e como o desphoto não está na BD temos que criar uma "camada" que vai tratar e retornar os objetos (fotos).

	{
		foreach($list as &$row) {    

			$p = new Product();
			$p->setData($row);
			$row = $p->getValues();  // Aqui já tem os valores . Coloca-os en $row que como em cima tem &$row, substitui os valores em cima e coloca-os em $list (é a Manipulação da memória).
		} 

		return $list;  // retorna $list com os dados de cada produto já formatado.
	}


	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>$this->getvlprice(),
			":vlwidth"=>$this->getvlwidth(),
			":vlheight"=>$this->getvlheight(),
			":vllength"=>$this->getvllength(),
			":vlweight"=>$this->getvlweight(),
			":desurl"=>$this->getdesurl()
		)); // chamada de procedure (ver mySql Workbench).

	
		$this->setData($results[0]);

	}


	public function get($idproduct)
	{
		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct",[
			":idproduct"=>$idproduct
		]);

		$this->setData($results[0]);

	}


	public function delete()
	{
		$sql = new Sql();
		$sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [
			":idproduct"=>$this->getidproduct()
		]);

	}

	public function checkPhoto()
	{
		if (file_exists(
			$_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
			"res" . DIRECTORY_SEPARATOR .
			"site" . DIRECTORY_SEPARATOR .
			"img" . DIRECTORY_SEPARATOR .
			"products" . DIRECTORY_SEPARATOR .
			$this->getidproduct() . ".jpg"
		)) {
			$url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";  // Uso / em vez de DIRECTORY_SEPARATOR, porque aqui é uma URL e não um caminho para a pasta.
		} else {
			$url = "/res/site/img/product.jpg";
		}	

		return $this->setdesphoto($url);
	}

	public function getValues()   // este getValues sobreescreve o getValues de Model para carregar a foto que não está guardada na base de dados mas sim num diretorio especifico, ou entao é carregada de um lugar e então guardada no diretório próprio.
	{
		$this->checkPhoto();

		$values = parent::getValues();

		return $values;
	}


	public function setPhoto($file)
	{
		if (empty($file["name"]))
		{
			$this->checkPhoto();
		} else 
			{
				$extension = explode(".", $file["name"]);  // agarra o nome do arquivo onde tem . e faz um array dele.

				$extension = end($extension); // informo que a $extension é só a ultima posicao do array.
				switch ($extension) {
					case "jpg":
					case "jpeg":
					$image = imagecreatefromjpeg($file["tmp_name"]);  // passa a variavel $file que veio no parametro com o array  ["tmp_name"] que é o mome temporário do arquivo que está no servidor.
					break;

					case "gif":
					$image = imagecreatefromgif($file["tmp_name"]);
					break;

					case "png":
					$image = imagecreatefrompng($file["tmp_name"]);
					break;
					
					default:
					throw new \Exception("<strong>Tipo de ficheiro inválido. Deve ser do tipo jpg, jpeg, gif ou png</strong>");
					}

				$dist = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
					"res" . DIRECTORY_SEPARATOR .
					"site" . DIRECTORY_SEPARATOR .
					"img" . DIRECTORY_SEPARATOR .
					"products" . DIRECTORY_SEPARATOR .
					$this->getidproduct() . ".jpg";


				imagejpeg($image, $dist);  // gerar imagem.jpg no diretorio e nome de ficheiro $dist.

				imagedestroy($image);

				$this->checkPhoto();  // para carregar o dado da memoria para o desphoto.

			}	

	}


	public function getFromURL($desurl)
	{

		$sql = new Sql();

		$rows = $sql->select("SELECT * FROM tb_products WHERE desurl =:desurl", [
			"desurl"=>$desurl
		]);

		$this->setData($rows[0]);

	}


	public function getCategories()
	{
		$sql = new Sql();

		return $sql->select("
			SELECT * FROM tb_categories a INNER JOIN tb_productscategories b ON a.idcategory = b.idcategory WHERE b.idproduct = :idproduct",
			[
				":idproduct"=>$this->getidproduct()
			]);


	}


	public function getProductsPage($page = 1, $itemsPerPage = 8)
	{
		$start = ($page -1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_products a 
			INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
			INNER JOIN tb_categories c ON c.idcategory = b.idcategory
			WHERE c.idcategory = :idcategory
			LIMIT $start, $itensPerPage;
			", [
				':idcategory'=>$this->getidcategory()
			]);

		$resultsTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return [
			'data'=>Product::checkList($results),
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];
	}




	public static function getPage($page = 1, $itemsPerPage = 10)
	{
		$start = ($page -1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_products
			ORDER BY desproduct
			LIMIT $start, $itemsPerPage;
			");

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];
	}


	public static function getPageSearch($search, $page = 1, $itemsPerPage = 10)
	{
		$start = ($page -1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_products 
			WHERE desproduct LIKE :search
			ORDER BY desproduct  
			LIMIT $start, $itemsPerPage;
			", [
				':search'=>'%'.$search.'%'
			]);

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return [
			'data'=>$results,
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];
	}



}


 ?>
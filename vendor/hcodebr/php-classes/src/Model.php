<?php 

namespace Hcode;

class Model {

	private $values = [];   // $values vai ter todos os valores dos campos que existem dentro do objeto, neste caso do usuario.

	public function __call($name, $args) {    // (__call) Metodo magico para saber quando o método é chamado. Recebe o nome e os argumentos passados.

		$method = substr($name, 0, 3);  // Para saber se é metodo GET ou SET. Comeca na posicao 0 quantos caracteres lê.

		$fieldName = substr($namw, 3, strlen($name)); // Para saber qual é o campo. Comeca na posicao 3 e vai ate ao fim.

		switch ($method)
		{
			case "get":
				return $this->values[$fieldName];
			break;
			case "set":
				$this->values[$fieldName] = $args[0];  // $args é o valor passado no atributo (ex: idusuario = 5).
			break;
		}

	}

	public function setData($data = array())
	{
		foreach ($data as $key => $value) {
			$this->{"set".$key}($value);
		}
	}


	public function getValues()
	{
		return $this->values;
	}




}


 ?>
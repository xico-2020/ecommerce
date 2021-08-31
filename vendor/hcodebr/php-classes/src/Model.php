<?php 

namespace Hcode;     // está no namespace principal.

class Model {

	private $values = [];   // $values vai receber todos os valores dos campos que existem dentro do objeto, neste caso do usuario.

	public function __call($name, $args) {    // (__call) Metodo magico para saber quando o método é chamado. Recebe o nome e os argumentos passados.   (ex: setidusuario)

		$method = substr($name, 0, 3);  // Para saber se é metodo GET ou SET. Comeca na posicao 0 quantos caracteres lê.   (set)

		$fieldName = substr($name, 3, strlen($name)); // Para saber qual é o campo. Comeca na posicao 3 e vai ate ao fim.     (idusuario)

		switch ($method)
		{
			case "get":
				return (isset($this->values[$fieldName])) ? $this->values[$fieldName] : NULL;
			break;
			case "set":
				$this->values[$fieldName] = $args[0];  // $args é o valor passado no atributo (ex: idusuario = 5).
			break;
		}

	}

	public function setData($data = array())
	{
		foreach ($data as $key => $value) {
			$this->{"set".$key}($value);  // {"set".$key} - Entre {} para ser dinamico e "set".$key é a concatenação do tipo de metodo (get ou set) com o nome (idusuario , neste caso)
		}
	}


	public function getValues()
	{
		return $this->values;    // retorna o atributo $this->values. Não fazemos o acesso direto ao atributo porque é privado (private $values) e por seguranca.
	}




}


 ?>
<?php 

namespace Hcode;

use Rain\Tpl;  // Quando for chamado o "new Tpl" ele esta no namespace Rain.
use \Hcode\Model\User;

class Page {

	private $tpl;     // criar atributo $tpl por forma a poder usar como atributo da classe masi abaixo.
	private $options = [];
	private $defaults = [    // Para o caso de ter opcoes padrao (defaults).
		"header"=>true,
		"footer"=>true,
		"data"=>[]   // dados que vao passar para o template (tambem um array)
	];


	public function __construct($opts = array(), $tpl_dir = "/views/") {    // As variaveis vao passando conforme as rotas do SLIM e dependendo da rota é que se vao passar os dados para a classe Page. Define-se para isso uma variavel $opts que é um array, mesmo que náo passe nada. Segundo parametro ($tpl_dir) indica que por padrao, se nao indicar nada é o diretorio views.

		$this->options = array_merge($this->defaults, $opts);  // array_merge junta dois arrays. e o ultimo subscreve os anteriores. Como quero que o valor passado no construct ($opts) subscreva o $defaults. Se houver conflito, vale o $opts. Se não houver conflito todos os valores são guardados no $options. Se header e footer Forem passados como false, desabilita porque sobreescreve.
		
		$config = array(    // tpl (templates) precisa de uma pasta para os arquivos HTML e outra para a cache. 
			"tpl_dir"=> $_SERVER["DOCUMENT_ROOT"]. $tpl_dir,      //  A  partir do nosso diretorio de root do nosso projeto vai procurar a pasta ..., Para isso uso varivavel de ambiente no Server que é a "DOCUMENT_ROOT" que traz essa pasta. Depois indico a pasta onde estão os templates. A seguir o mesmo para cache.
			"cache_dir"=> $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
			"debug"=> false // set to false to improve the speed
		);

		Tpl::configure( $config );

		$this->tpl = new Tpl;  // para ter acesso a $tpl nos outros metodos é melhor ser um atributo da classe ($this->$tpl).

		if (isset($_SESSION[User::SESSION]))  
			{
				$this->tpl->assign("user", $_SESSION[User::SESSION]);
				
			}  // Para que na pagina de Administracao apareca o nome do usario logado. Deve-se substituir no header.html o nome de Francisco Paulo por {$user.desperson}. Não implementado ho HTML pois dá erro ...

		$this->setData($this->options["data"]);

		
		if ($this->options["header"] === true)  $this->tpl->draw("header");   // draw é um método de tpl. Só executa o draw do header caso seja true.


	}

	private function setData($data = array()) {
		foreach ($data as $key => $value) {
			$this->tpl->assign($key, $value);
		}
	}


	public function setTpl($name, $data = array(), $returnHTML = false) {
		$this->setData($data);

		return $this->tpl->draw($name, $returnHTML);
	}
	

	public function __destruct() {

		if ($this->options["footer"] === true) $this->tpl->draw("footer");


	}

}



?>
<?php 

namespace Hcode;

class PageAdmin extends Page {

	public function __construct($opts = array(), $tpl_dir = "/views/admin/") {     // Para que os layout de Admin sejam carregados da pasta view/admin.

		parent::__construct($opts, $tpl_dir);  // para aproveitar o metodo construtor da classe pai (page) passando o $opts e $tpl_dir. Aplicado o conceito de HERANCA.
	}
}



 ?>
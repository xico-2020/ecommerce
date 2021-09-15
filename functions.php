<?php 

use \Hcode\Model\User;

function formatPrice($vlprice)
{
	return number_format($vlprice, 2, ",", ".");
}


function checkLogin($inadmin = true)  //  Passa o inadmin que por padrao é true e envia para a classe User metodo checkLogin. Isto para poder usar dentro do template no namespace global. Usado no header.html
{
	return User::checkLogin($inadmin);
}


function getUserName()    //  Serve para ver o nome do user na logado na sessão.
{
	$user = User::getFromSession();
	return $user->getdesperson();
}


 ?>
<?php 

use \Hcode\Model\User;
use \Hcode\Model\Cart;

function formatPrice($vlprice)
{
	if (!$vlprice > 0) $vlprice = 0.0;

	return number_format($vlprice, 2, ",", ".");
}

function formatDate($date)
{

	return date('d/m/Y' , strtotime($date));
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


function getCartNrQtd()
{

	$cart = Cart::getFromSession();

	$totals = $cart->getProductsTotals();

	return $totals['nrqtd'];

}


function getCartVlSubTotal()
{

	$cart = Cart::getFromSession();

	$totals = $cart->getProductsTotals();

	return formatPrice($totals['vlprice']);

}





 ?>
<?php
/**
 * (c) Gabriel Quagliano - gabriel.quagliano@gmail.com
 */

//Email de la cuenta de Let's Encrypt
$cliente='';

//Listado de cuentas a procesar, formato [ 'dominio principal'=>(object)[ parametros ] ]
$cuentas=[
	'prueba.com'=>(object)[
		'dominios'=>['prueba.com','www.prueba.com'], //Opcional
		'ftp'=>'prueba.com' //Opcional
		'ruta'=>'/public_html/', //Opcional
		'usuario'=>'usuario',
		'contrasena'=>'contraseña'
	]
];

//Listado de cuentas a procesar, por nombre ['test.com','otraprueba.com']. Si se omite, se procesarán todas las cuentas
$procesar=null;

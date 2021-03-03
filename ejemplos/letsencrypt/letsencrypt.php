<?php
/**
 * (c) Gabriel Quagliano - gabriel.quagliano@gmail.com
 */

use ferozo\ferozo;
include(__DIR__.'/../../ferozo.php');
include(__DIR__.'/obtener.php');
include(__DIR__.'/config.php');

if(!$procesar) {
	foreach($cuentas as $dominio=>$opciones)
		$procesar[]=$dominio;
}

foreach($procesar as $dominio) {
	$opciones=$cuentas[$dominio];

	echo 'Procesando '.$dominio.'...'.PHP_EOL;

	$cert=obtenerCertificado($cliente,$dominio,$opciones);
	if(!$cert) continue;

	echo 'Instalando certificado...'.PHP_EOL;

	$ferozo=new ferozo;
	if(!$ferozo->iniciarSesion($opciones->usuario,$opciones->contrasena)) {
	    echo 'Error de inicio de sesión.'.PHP_EOL;
	    continue;
	}

	$ssl=$ferozo->instalarSsl(
		file_get_contents(__DIR__.'/certificados/'.$dominio.'.crt'),
		file_get_contents(__DIR__.'/certificados/'.$dominio.'.key')
	);
	if($ssl!==true) {
		echo 'Fallo la instalacion';
		if(is_string($ssl)) echo ': '.$ssl;
		echo '.'.PHP_EOL;
		continue;
	}

	echo 'Listo.'.PHP_EOL;
}



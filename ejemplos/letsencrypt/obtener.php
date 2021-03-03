<?php
/**
 * (c) Gabriel Quagliano - gabriel.quagliano@gmail.com
 */

include(__DIR__.'/leclient/vendor/autoload.php');

use LEClient\LEClient;
use LEClient\LEOrder;

function obtenerCertificado($cuenta,$dominio,$opciones,$log=false) {
	$cliente=new LEClient([$cuenta],LEClient::LE_PRODUCTION,$log?LEClient::LOG_STATUS:LEClient::LOG_OFF);

	echo 'Procesando certificado para '.$dominio.'...'.PHP_EOL;

	$dominios=$opciones->dominios?$opciones->dominios:[$dominio,'www.'.$dominio];

	$orden=$cliente->getOrCreateOrder($dominio,$dominios);
	$pendiente = $orden->getPendingAuthorizations(LEOrder::CHALLENGE_TYPE_HTTP);
	if(!empty($pendiente)) {
		foreach($pendiente as $i=>$reto) {
			echo 'Resolviendo reto #'.$i.'...'.PHP_EOL;

			$servidor=$opciones->ftp?$opciones->ftp:$dominio;
			$ftp=ftp_connect($servidor);
			$ingreso=ftp_login($ftp,$opciones->usuario,$opciones->contrasena);
			if(!$ingreso) {
				echo 'FTP: Error de acceso.'.PHP_EOL;
				ftp_close($ftp);
				break;
			}

			ftp_pasv($ftp,true);

			$rutaBase=$opciones->ruta?$opciones->ruta:'/public_html/';

			$ruta=$rutaBase;
			$chdir=ftp_chdir($ftp,$ruta);
			if(!$chdir) {
				echo 'FTP: Ruta inexistente.'.PHP_EOL;
				ftp_close($ftp);
				break;
			}

			$ruta.='.well-known/';
			$chdir=ftp_chdir($ftp,$ruta);
			if(!$chdir) {
				ftp_mkdir($ftp,$ruta);
				$chdir=ftp_chdir($ftp,$ruta);
				if(!$chdir) {
					echo 'FTP: Error al crear el directorio (1).'.PHP_EOL;
					ftp_close($ftp);
					break;
				}
			}

			$ruta.='acme-challenge/';
			$chdir=ftp_chdir($ftp,$ruta);
			if(!$chdir) {
				ftp_mkdir($ftp,$ruta);
				$chdir=ftp_chdir($ftp,$ruta);
				if(!$chdir) {
					echo 'FTP: Error al crear el directorio (2).'.PHP_EOL;
					ftp_close($ftp);
					break;
				}
			}

			$temp=__DIR__.'/temp';
			file_put_contents($temp,$reto['content']);
			$f=fopen($temp,'r');

			$subida=ftp_fput($ftp,$ruta.$reto['filename'],$f,FTP_ASCII);
			if(!$subida) {
				echo 'FTP: Error al subir el archivo.'.PHP_EOL;
				ftp_close($ftp);
				break;				
			}

			fclose($f);

			ftp_rename($ftp,$rutaBase.'.htaccess',$rutaBase.'_.htaccess');

			$orden->verifyPendingOrderAuthorization($reto['identifier'], LEOrder::CHALLENGE_TYPE_HTTP);

			ftp_rename($ftp,$rutaBase.'_.htaccess',$rutaBase.'.htaccess');

			ftp_close($ftp);
		}
	}

	if($orden->allAuthorizationsValid()) {
		if(!$orden->isFinalized()) $orden->finalizeOrder();
		if($orden->isFinalized()) $orden->getCertificate();
	
		copy(__DIR__.'/keys/certificate.crt',__DIR__.'/certificados/'.$dominio.'.crt');
		copy(__DIR__.'/keys/private.pem',__DIR__.'/certificados/'.$dominio.'.key');

		echo 'Listo.'.PHP_EOL;
		return true;
	}
	
	echo 'Fallo la autorizacion.'.PHP_EOL;
	return false;
}
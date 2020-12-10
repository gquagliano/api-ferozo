<?php
/**
 * (c) Gabriel Quagliano - gabriel.quagliano@gmail.com
 */

use ferozo\ferozo;
include(__DIR__.'/ferozo.php');
include(__DIR__.'/config.php');

$ferozo=new ferozo;
if(!$ferozo->iniciarSesion(_usuario,_contrasena)) {
    echo 'Error de inicio de sesión.';
    exit;
}

var_dump($ferozo->obtenerDominios());

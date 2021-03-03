### Instalar yourivw/leclient

    cd leclient
    composer require yourivw/leclient

### Instalar OpenSSL

Instalar OpenSSL y verificar que la extensión PHP esté habilitada.

### Configurar cURL

Verificar que la extensión cURL esté habilitada y configurar `curl.cainfo` en el `php.ini`. Puede obtenerse un certificado en https://curl.se/docs/caextract.html

### Configurar las cuentas

Crear una copia de `config-ejemplo.php` en `config.php` y configurar las cuentas de acuerdo a los comentarios en el archivo.

### Ejecutar

    php letsencrypt.php


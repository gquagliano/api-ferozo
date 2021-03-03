<?php
/**
 * (c) Gabriel Quagliano - gabriel.quagliano@gmail.com
 */

namespace ferozo;

include_once(__DIR__.'/ferozoBase.php');

/**
 * Interfaz con el panel de control de hosting Ferozo.
 */
class ferozo extends ferozoBase {
    public function obtenerDominios($pagina=1) {
        $this->csrf();
        $res=$this->postJson('hosting/domain/listdomains',[
            'params'=>[
                'pagination'=>[
                    'page'=>$pagina,
                    'offset'=>50,
                    'orderBy'=>'',
                    'orderType'=>''
                ]
            ]
        ]);
        if($res) return $res->result;
        return null;
    }

    public function instalarSsl($certificado,$privada) {
        $this->csrf();
        $res=$this->postJson('hosting/domain/getsslcertinfo',[
            'params'=>[
                'crt'=>$certificado,
                'key'=>$privada
            ]
        ]);
        if($res->error) return $res->error->data->inputException[0]->errorDesc;
        if($res&&!$res->error) {
        	$dominio=$res->result->domain;
        	$dominios=$res->result->altDomain;
        	$this->csrf();
        	$res=$this->postJson('hosting/domain/installsslcrtkey',[
        		'params'=>[
        			'crt'=>$certificado,
					'domain'=>$dominio,
					'domainAlt'=>$dominios,
					'forcedhttps'=>0,
					'key'=>$privada
				]
        	]);
        	if($res&&!$res->error) return true;
        }
        return null;
    }
}
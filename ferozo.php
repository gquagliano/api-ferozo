<?php
/**
 * (c) Gabriel Quagliano - gabriel.quagliano@gmail.com
 */

namespace ferozo;

/**
 * Interfaz con el panel de control de hosting Ferozo.
 */
class ferozo {
    protected $base='https://mipanel.ferozo.com/';

    private $usuario;
    private $contrasena;

    protected $ultimoCodigo;
    protected $ultimosEncabezadosRespuesta;
    protected $cookies=null;
    protected $csrf=null;

    /**
     * Destructor.
     */
    function __destruct() {
        if($this->cookies&&file_exists($this->cookies)) unlink($this->cookies);
    }

    /**
     * Inicia la sesión.
     * @param string $usuario Nombre de usuario.
     * @param string $contrasena Contraseña.
     * @return bool
     */
    public function iniciarSesion($usuario,$contrasena) {
        $this->get('');
        $this->post('login_check',[
            '_username'=>$usuario,
            '_password'=>$contrasena
        ]);

        if($this->ultimoCodigo!=302||preg_match('#/login#',$this->ultimosEncabezadosRespuesta->location)) return false;

        $this->usuario=$usuario;
        $this->contrasena=$contrasena;
        return true;
    }

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
    
    protected function csrf() {
        $res=$this->getJson('common/security/csrf/token/get',null,[
            'Sec-Fetch-Dest'=>'empty',
            'Sec-Fetch-Mode'=>'cors',
            'Sec-Fetch-Site'=>'same-origin'
        ]);
        if($res) $this->tokenCsrf=$res->result->token;
    }

    /**
     * 
     */
    protected function get($ruta,$parametros=null,$encabezados=null) {
        if($parametros) {
            $ruta.=strpos($ruta,'?')===false?'?':'&';
            $ruta.=http_build_query($parametros);
        }
        return $this->solicitud('get',$ruta,null,null,$encabezados);
    }

    /**
     * 
     */
    protected function getJson($ruta,$parametros=null,$encabezados=null) {
        $listaEncabezados=[
            'Content-Type'=>'application/json',
            'Accept'=>'application/json, text/javascript, */*',
            'X-Requested-With'=>'XMLHttpRequest'
        ];
        if($encabezados) $listaEncabezados=array_merge($listaEncabezados,$encabezados);
        return json_decode($this->get($ruta,$parametros,$listaEncabezados));
    }

    /**
     * 
     */
    protected function post($ruta,$parametros=null,$cuerpo=null,$encabezados=null) {
        return $this->solicitud('post',$ruta,$parametros,$cuerpo,$encabezados);
    }


    /**
     * 
     */
    protected function postJson($ruta,$parametros=null,$encabezados=null) {
        $listaEncabezados=[
            'Content-Type'=>'application/json',
            'Accept'=>'application/json, text/javascript, */*',
            'X-Requested-With'=>'XMLHttpRequest'
        ];
        if($encabezados) $listaEncabezados=array_merge($listaEncabezados,$encabezados);
        return json_decode($this->post($ruta,null,json_encode($parametros),$listaEncabezados));
    }

    /**
     * 
     */
    protected function solicitud($metodo,$ruta,$parametros=null,$cuerpo=null,$encabezados=null,$opciones=null) {
        $curl=curl_init();

        if(!$this->cookies) $this->cookies=tempnam(__DIR__.'/temp','c');
        $this->ultimosEncabezadosRespuesta=(object)[];
        
        $listaEncabezados=[
            'User-Agent'=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36 OPR/72.0.3815.400',
            'Referer'=>$this->base,
            'Accept'=>'*/*'     
        ];
        if($this->tokenCsrf) $listaEncabezados['CSRF-Token']=$this->tokenCsrf;

        $opc=[
            CURLOPT_URL=>$this->base.$ruta,
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_HTTPHEADER=>[],
            CURLOPT_FOLLOWLOCATION=>false,
            CURLOPT_COOKIEFILE=>$this->cookies,
            CURLOPT_COOKIEJAR=>$this->cookies,
            CURLOPT_HEADERFUNCTION=>[$this,'procesarEncabezado']
        ];

        if($metodo=='post') {
            $opc[CURLOPT_POST]=true;
            if($cuerpo) {
                $opc[CURLOPT_POSTFIELDS]=$cuerpo;
            } elseif($parametros) {
                $listaEncabezados['Content-Type']='application/x-www-form-urlencoded';
                $opc[CURLOPT_POSTFIELDS]='_username=c2011072&_password=ZInidavu38';//http_build_query($parametros);
            }
        }
        
        if($encabezados) $listaEncabezados=array_merge($listaEncabezados,$encabezados);
        
        foreach($listaEncabezados as $c=>$v) $opc[CURLOPT_HTTPHEADER][]=$c.': '.$v;

        if($opciones) $opc=array_merge($opc,$opciones);

	    curl_setopt_array($curl,$opc);
    
        $resp=curl_exec($curl);
        
        $this->ultimoCodigo=curl_getinfo($curl,CURLINFO_HTTP_CODE);

        curl_close($curl);
        
    	return $resp;
    }

    /**
     * 
     */
    protected function procesarEncabezado($c,$linea) {
        $ret=strlen($linea);

        $linea=explode(':',$linea,2);
        if(count($linea)!=2) return $ret;

        $nombre=strtolower(trim($linea[0]));
        $valor=trim($linea[1]);

        $palabras=explode('-',$nombre);
        $nombre=$palabras[0];
        for($i=1;$i<count($palabras);$i++) $nombre.=ucfirst($palabras[$i]);

        if(isset($this->ultimosEncabezadosRespuesta->$nombre)) {
            $this->ultimosEncabezadosRespuesta->$nombre=[$this->ultimosEncabezadosRespuesta[$nombre]];
            $this->ultimosEncabezadosRespuesta->$nombre[]=$valor;
        } else {
            $this->ultimosEncabezadosRespuesta->$nombre=$valor;
        }
        return $ret;
    }
}
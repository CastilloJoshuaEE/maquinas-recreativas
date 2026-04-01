<?php
/**
 * maquinas_recreativas - Core Response
 * 
 * Abstracción de la respuesta HTTP.
 * 
 * @package maquinas_recreativas\Core
 * @author Tu Equipo
 * @version 1.0
 */
namespace maquinas_recreativas\Core;
class Response{
    private array $headers = [];
    private mixed $content = null;
    private int $statusCode=200;
    /**
     * Establece un header
     * 
     * @param string $name
     * @param string $value
     * @return self
     */
    public function header(string $name, string $value):self{
        $this->headers[$name] = $value;
        return $this;
    }    
    /**
     * Establece múltiples headers
     * 
     * @param array $headers
     * @return self
     */  
    public function withHeaders(array $headers):self{
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }  
    /**
     * Establece el código de estado
     * 
     * @param int $code
     * @return self
     */
    public function status(int $code):self{
        $this->statusCode = $code;
        return $this;
    }    
    /**
     * Establece el contenido
     * 
     * @param mixed $content
     * @return self
     */    
    public function content(mixed $content):self{
        $this->content = $content;
        return $this;
    }
    /**
     * Envía una respuesta JSON
     * 
     * @param mixed $data
     * @param int $statusCode
     * @return void
     */
public function json($data, int $statusCode = 200): self
{
    $this->status($statusCode);
    $this->header('Content-Type', 'application/json; charset=utf-8');
    $this->content = json_encode($data, JSON_UNESCAPED_UNICODE);
    return $this;
}
    /**
     * Envía la respuesta
     * 
     * @return void
     */    
    public function send():void{
        // Aplicar código de estado
        http_response_code($this->statusCode);
        // Enviar headers
        foreach($this->headers as $name => $value){
            header("$name:$value");
        }
        // Enviar contenido
        if($this->content!==null){
            echo $this->content;
        }
        exit;

    }
}
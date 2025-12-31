<?php
namespace App\Core;

/**
 * Classe para manipulação de respostas HTTP
 */
class Response
{
    private $statusCode = 200;
    private $headers = [];
    private $body = '';
    
    /**
     * Define o código de status HTTP
     */
    public function setStatusCode($code)
    {
        $this->statusCode = $code;
        return $this;
    }
    
    /**
     * Adiciona um header
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }
    
    /**
     * Define o conteúdo da resposta
     */
    public function setBody($content)
    {
        $this->body = $content;
        return $this;
    }
    
    /**
     * Envia resposta JSON
     */
    public function json($data, $statusCode = 200)
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'application/json; charset=utf-8');
        $this->body = json_encode($data, JSON_UNESCAPED_UNICODE);
        $this->send();
        exit;
    }
    
    /**
     * Redireciona para uma URL
     */
    public function redirect($url, $statusCode = 302)
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Location', $url);
        $this->send();
        exit;
    }
    
    /**
     * Envia a resposta
     */
    public function send($content = null)
    {
        if ($content !== null) {
            $this->body = $content;
        }
        
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
        
        echo $this->body;
    }
}


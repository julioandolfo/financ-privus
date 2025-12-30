<?php
namespace App\Core;

/**
 * Helper para views - permite usar $this->baseUrl() e $this->session nas views
 */
class ViewHelper
{
    private $controller;
    
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }
    
    public function baseUrl($path = '')
    {
        return $this->controller->baseUrl($path);
    }
    
    public function __get($name)
    {
        if ($name === 'session') {
            return $this->controller->session;
        }
        return null;
    }
}


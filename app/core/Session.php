<?php
namespace App\Core;

/**
 * Gerenciamento de sessões
 */
class Session
{
    private static $started = false;
    
    /**
     * Inicia a sessão
     */
    public static function start()
    {
        if (!self::$started) {
            $config = require __DIR__ . '/../../config/config.php';
            
            session_name($config['session_name']);
            session_set_cookie_params([
                'lifetime' => $config['session_lifetime'],
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            session_start();
            self::$started = true;
        }
    }
    
    /**
     * Define um valor na sessão
     */
    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    /**
     * Retorna um valor da sessão
     */
    public static function get($key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Verifica se uma chave existe na sessão
     */
    public static function has($key)
    {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove um valor da sessão
     */
    public static function remove($key)
    {
        self::start();
        unset($_SESSION[$key]);
    }
    
    /**
     * Alias para remove (para compatibilidade)
     */
    public static function delete($key)
    {
        return self::remove($key);
    }
    
    /**
     * Limpa toda a sessão
     */
    public static function clear()
    {
        self::start();
        $_SESSION = [];
    }
    
    /**
     * Destrói a sessão
     */
    public static function destroy()
    {
        self::start();
        session_destroy();
        self::$started = false;
    }
    
    /**
     * Define mensagem flash
     */
    public static function flash($key, $value)
    {
        self::set("_flash_{$key}", $value);
    }
    
    /**
     * Retorna e remove mensagem flash
     */
    public static function getFlash($key, $default = null)
    {
        $value = self::get("_flash_{$key}", $default);
        self::remove("_flash_{$key}");
        return $value;
    }
}


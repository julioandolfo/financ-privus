<?php
/**
 * Carrega variáveis de ambiente do arquivo .env
 */
class EnvLoader
{
    /**
     * Carrega variáveis do arquivo .env
     */
    public static function load($path = null)
    {
        if ($path === null) {
            $path = dirname(__DIR__) . '/.env';
        }
        
        if (!file_exists($path)) {
            return;
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Ignora comentários
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Separa chave e valor
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove aspas se existirem
                $value = trim($value, '"\'');
                
                // Define variável de ambiente se não existir
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    putenv("{$key}={$value}");
                }
            }
        }
    }
}


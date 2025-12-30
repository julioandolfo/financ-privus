<?php
/**
 * Redireciona para public/index.php
 * Arquivo necessário para evitar erro 403
 */

// Redireciona para public/
header('Location: /public/index.php');
exit;


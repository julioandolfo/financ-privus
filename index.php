<?php
/**
 * Entry point na raiz - Carrega o sistema da pasta public/
 * Este arquivo existe porque o cPanel aponta para public_html e não para public_html/public
 */

// Carrega o index.php da pasta public/
require __DIR__ . '/public/index.php';


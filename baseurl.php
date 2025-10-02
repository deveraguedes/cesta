<?php
// Caminho base absoluto da aplicação para construção de URLs site‑relativas
// Ajuste se a aplicação for servida sob outro prefixo.
if (!defined('BASE_PATH')) {
    define('BASE_PATH', '/cesta/');
}

/**
 * Constrói uma URL absoluta (site‑relativa) a partir do caminho informado.
 * Ex.: abs_url('beneficiario.php') => '/cesta/beneficiario.php'
 */
if (!function_exists('abs_url')) {
    function abs_url($path = '') {
        $p = ltrim((string)$path, '/');
        return BASE_PATH . $p;
    }
}
?>
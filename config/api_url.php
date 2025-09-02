<?php
// Base API URL (bisa di-override lewat environment variable API_BASE_URL)
$API_BASE_URL = getenv('API_BASE_URL') !== false ? rtrim(getenv('API_BASE_URL'), '/') . '/' : 'http://localhost:8012/Dashboard_Business/src/api/';

/**
 * Kembalikan URL penuh untuk sebuah endpoint file API.
 * Contoh: getApiUrl('register.php') -> http://.../src/api/register.php
 */
function getApiUrl($filename) {
    global $API_BASE_URL;
    return rtrim($API_BASE_URL, '/') . '/' . ltrim($filename, '/');
}

function getBaseApiUrl() {
    global $API_BASE_URL;
    return rtrim($API_BASE_URL, '/') . '/';
}

// helper: jika ingin override di runtime
function setBaseApiUrl($url) {
    global $API_BASE_URL;
    $API_BASE_URL = rtrim($url, '/') . '/';
}
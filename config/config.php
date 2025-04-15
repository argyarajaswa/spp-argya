<?php
// Pengaturan dasar aplikasi
define('APP_NAME', 'MySPP');
define('APP_URL', 'http://localhost/myspp');
define('APP_EMAIL', 'arrajzgay@gmail.com');

// Pengaturan waktu
date_default_timezone_set('Asia/Jakarta');

// Pengaturan session
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
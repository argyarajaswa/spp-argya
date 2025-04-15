<?php
if (!isset($_SESSION['user'])) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Fungsi untuk memeriksa level akses
function checkAccess($requiredLevel) {
    if ($_SESSION['user']['level'] !== $requiredLevel) {
        header('Location: ' . APP_URL . '/admin/dashboard.php');
        exit;
    }
}
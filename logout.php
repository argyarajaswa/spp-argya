<?php
require_once __DIR__ . '/config/config.php';

session_unset();
session_destroy();

header('Location: ' . APP_URL . '/login.php');
exit;
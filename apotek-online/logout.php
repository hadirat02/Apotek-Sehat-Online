<?php require 'config.php';
if (user()) logact('Logout');
$_SESSION = []; session_destroy(); header('Location: ' . BASE . '/'); exit;

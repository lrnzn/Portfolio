<?php

// Mark that this request came through the router
define('LIBROTRACK', true);

// Suppress notices and warnings — errors still show for fatal issues
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

$controller = $_GET['controller'] ?? 'Auth';
$action = $_GET['action'] ?? 'login';

$controllerName = $controller . "Controller";
$controllerFile = "../app/controllers/" . $controllerName . ".php";

if (!file_exists($controllerFile)) {
    header("Location: index.php?controller=Auth&action=login");
    exit;
}

require_once $controllerFile;

$controllerObj = new $controllerName();

if (!method_exists($controllerObj, $action)) {
    die("Action not found.");
}

$controllerObj->$action();
?>
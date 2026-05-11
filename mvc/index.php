<?php
session_start();

require_once "controllers/UserController.php";
require_once "controllers/ReservaController.php";

$action = $_GET['action'] ?? 'loginView';

switch($action){

    case 'loginView':    require "views/login.php";    break;
    case 'registerView': require "views/register.php"; break;
    case 'dashboard':    require "views/dashboard.php"; break;

    case 'login':    (new UserController())->login();    break;
    case 'register': (new UserController())->register(); break;

    case 'guardarReserva':  (new ReservaController())->guardar();      break;
    case 'listarReservas':  (new ReservaController())->listar();       break;
    case 'habitaciones':    (new ReservaController())->habitaciones(); break;
    case 'cancelarReserva': (new ReservaController())->cancelar();     break;

    // Reportes
    case 'pdfReserva':     (new ReservaController())->pdfReserva();     break;
    case 'excelReservas':  (new ReservaController())->excelReservas();  break;

    default: require "views/login.php";
}
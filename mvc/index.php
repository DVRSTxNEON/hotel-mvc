<?php
session_start();

require_once "controllers/UserController.php";
require_once "controllers/ReservaController.php";

// TOAST GLOBAL
function setToast($msg, $type="success"){
    $_SESSION['toast'] = [
        "msg"=>$msg,
        "type"=>$type
    ];
}

$action = $_GET['action'] ?? 'login';

switch($action){

case 'login':
    require "views/login.php";
break;

case 'register':
    require "views/register.php";
break;

case 'loginUser':
    (new UserController())->login();
break;

case 'registerUser':
    (new UserController())->register();
break;

case 'dashboard':
    require "views/dashboard.php";
break;

case 'guardarReserva':
    (new ReservaController())->guardar();
break;

case 'habitaciones':
    (new ReservaController())->habitacionesPorClase();
break;

case 'editarReserva':
    $reserva = (new ReservaController())->editar();
    require "views/dashboard.php";
break;

case 'actualizarReserva':
    (new ReservaController())->actualizar();
break;

case 'eliminarReserva':
    (new ReservaController())->eliminar();
break;

case 'logout':
    session_destroy();
    header("Location:index.php");
break;

}
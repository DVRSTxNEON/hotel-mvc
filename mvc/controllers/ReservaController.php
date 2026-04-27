<?php
require_once "models/Reserva.php";

class ReservaController{

public function guardar(){

    $fi = $_POST['fecha_inicio'];
    $ff = $_POST['fecha_fin'];
    $hoy = date("Y-m-d");

    if($fi < $hoy){
        setToast("No puedes usar fechas pasadas","error");
        header("Location:index.php?action=dashboard");
        exit;
    }

    if($fi > $ff){
        setToast("La fecha inicio no puede ser mayor a la final","error");
        header("Location:index.php?action=dashboard");
        exit;
    }

    $r = new Reserva();
    $r->crear($_SESSION['user']['id'], $_POST['habitacion'], $fi, $ff);

    setToast("Reserva creada correctamente");
    header("Location:index.php?action=dashboard");
}

public function listar(){
    $r = new Reserva();
    return $r->obtenerPorUsuario($_SESSION['user']['id']);
}

public function eliminar(){
    $r = new Reserva();
    $r->eliminar($_GET['id']);

    setToast("Reserva eliminada");
    header("Location:index.php?action=dashboard");
}

public function editar(){
    $r = new Reserva();
    return $r->obtenerUno($_GET['id']);
}

public function actualizar(){

    $fi = $_POST['fecha_inicio'];
    $ff = $_POST['fecha_fin'];
    $hoy = date("Y-m-d");

    if($fi < $hoy){
        setToast("No fechas pasadas","error");
        header("Location:index.php?action=dashboard");
        exit;
    }

    if($fi > $ff){
        setToast("Rango inválido","error");
        header("Location:index.php?action=dashboard");
        exit;
    }

    $r = new Reserva();
    $r->actualizar($_POST['id'], $_POST['habitacion'], $fi, $ff);

    setToast("Reserva actualizada");
    header("Location:index.php?action=dashboard");
}

public function habitacionesPorClase(){
    $clase = $_GET['clase'];

    $r = new Reserva();
    $data = $r->obtenerHabitacionesPorClase($clase);

    echo json_encode($data);
}

}
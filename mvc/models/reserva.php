<?php
require_once "config/db.php";

class Reserva{

private $db;

public function __construct(){
    $this->db = DB::connect();
}

public function crear($uid,$h,$fi,$ff){
    $q=$this->db->prepare("
        INSERT INTO reservas(usuario_id,habitacion,fecha_inicio,fecha_fin)
        VALUES(?,?,?,?)
    ");
    $q->execute([$uid,$h,$fi,$ff]);
}

public function obtenerPorUsuario($id){
    $q=$this->db->prepare("
        SELECT * FROM reservas
        WHERE usuario_id=? AND deleted_at IS NULL
        ORDER BY id DESC
    ");
    $q->execute([$id]);
    return $q->fetchAll(PDO::FETCH_ASSOC);
}

public function eliminar($id){
    $q=$this->db->prepare("UPDATE reservas SET deleted_at=NOW() WHERE id=?");
    $q->execute([$id]);
}

public function obtenerUno($id){
    $q=$this->db->prepare("SELECT * FROM reservas WHERE id=?");
    $q->execute([$id]);
    return $q->fetch(PDO::FETCH_ASSOC);
}

public function actualizar($id,$h,$fi,$ff){
    $q=$this->db->prepare("
        UPDATE reservas SET habitacion=?, fecha_inicio=?, fecha_fin=?
        WHERE id=?
    ");
    $q->execute([$h,$fi,$ff,$id]);
}

public function obtenerHabitacionesPorClase($clase){
    $q = $this->db->prepare("SELECT * FROM habitaciones WHERE clase=?");
    $q->execute([$clase]);
    return $q->fetchAll(PDO::FETCH_ASSOC);
}

}
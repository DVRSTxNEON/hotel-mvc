<?php
require_once "config/db.php";

class User{

private $db;

public function __construct(){
    $this->db = DB::connect();
}

public function existeEmail($email){
    $q=$this->db->prepare("SELECT id FROM users WHERE email=?");
    $q->execute([$email]);
    return $q->fetch();
}

public function crear($n,$e,$p,$c){
    $q=$this->db->prepare("INSERT INTO users(nombre,email,password,cedula) VALUES(?,?,?,?)");
    $q->execute([$n,$e,$p,$c]);
}

public function login($email){
    $q=$this->db->prepare("SELECT * FROM users WHERE email=?");
    $q->execute([$email]);
    return $q->fetch(PDO::FETCH_ASSOC);
}

public function existeCedula($cedula){
    $q = $this->db->prepare("SELECT id FROM users WHERE cedula=?");
    $q->execute([$cedula]);
    return $q->fetch();
}

}
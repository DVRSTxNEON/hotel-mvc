<?php
require_once "models/User.php";

class UserController{

public function register(){

    // VALIDACIONES

    if(preg_match('/[0-9]/', $_POST['nombre'])){
        setToast("El nombre no puede tener números","error");
        header("Location:index.php?action=register");
        exit;
    }

    if(!ctype_digit($_POST['cedula'])){
        setToast("La cédula solo puede tener números","error");
        header("Location:index.php?action=register");
        exit;
    }

    if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
        setToast("Correo inválido","error");
        header("Location:index.php?action=register");
        exit;
    }

    $pass = $_POST['password'];

    if(
        strlen($pass) < 6 ||
        !preg_match('/[A-Z]/',$pass) ||
        !preg_match('/[a-z]/',$pass) ||
        !preg_match('/[\W]/',$pass)
    ){
        setToast("Contraseña insegura","error");
        header("Location:index.php?action=register");
        exit;
    }

    $u = new User();

    if($u->existeEmail($_POST['email'])){
        setToast("El correo ya está registrado","error");
        header("Location:index.php?action=register");
        exit;
    }

    if($u->existeCedula($_POST['cedula'])){
    setToast("La cédula ya está registrada","error");
    header("Location:index.php?action=register");
    exit;
}

    $u->crear(
        $_POST['nombre'],
        $_POST['email'],
        password_hash($pass,PASSWORD_DEFAULT),
        $_POST['cedula']
    );

    setToast("Cuenta creada correctamente");
    header("Location:index.php");
}

public function login(){

    $u = new User();
    $user = $u->login($_POST['email']);

    if($user && password_verify($_POST['password'],$user['password'])){
        $_SESSION['user']=$user;
        header("Location:index.php?action=dashboard");
    }else{
        setToast("Credenciales incorrectas","error");
        header("Location:index.php");
    }
}

}
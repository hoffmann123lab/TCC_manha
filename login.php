<?php

session_start();

require "connection.php";

$email = trim($_POST["email"] ?? "");
$senha = $_POST["senha"] ?? "";

if ($email === "" || $senha === "") {

    header("Location: index.php?erro=" . urlencode("Preencha todos os campos."));
    exit;

}

$stmt = $conn->prepare(
    "SELECT id_fun, nome_fun, email, senha, tipo
     FROM funcionario
     WHERE email = ?"
);

if (!$stmt) {

    header("Location: index.php?erro=" . urlencode("Erro ao acessar o banco de dados."));
    exit;

}

$stmt->bind_param("s", $email);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {

    header("Location: index.php?erro=" . urlencode("E-mail não encontrado."));
    exit;

}

$dados = $resultado->fetch_assoc();

if (!password_verify($senha, $dados["senha"])) {

    header("Location: index.php?erro=" . urlencode("Senha incorreta."));
    exit;

}

$_SESSION["id_fun"] = $dados["id_fun"];
$_SESSION["nome_fun"] = $dados["nome_fun"];
$_SESSION["email"] = $dados["email"];
$_SESSION["tipo"] = $dados["tipo"];

$stmt->close();

header("Location: inicio.php");
exit;

?>
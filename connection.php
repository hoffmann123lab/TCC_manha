<?php

$host = "localhost";
$user = "root";
$senha = "";
$banco = "sigep_epi";

$conn = new mysqli($host, $user, $senha, $banco);

if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados.");
}

$conn->set_charset("utf8mb4");

?>
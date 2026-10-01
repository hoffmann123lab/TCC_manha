<?php

    require "connection.php";

    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    $stmt = $conn->prepare("SELECT senha FROM Funcionario WHERE email = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 0) {
        echo "Usuário não encontrado.";
        exit;
    }

    $dados = $resultado->fetch_assoc();

    if (password_verify($senha, $dados['senha'])) {
        echo "Login realizado com sucesso.";
    } else {
        echo "Senha incorreta.";
    }

    $stmt->close();
    $conn->close();

?>
<?php
require "connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $idade = $_POST['idade'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $tipo = $_POST['tipo'];
    $idCargo = $_POST['idCargo'];

    $stmt = $conn->prepare("INSERT INTO Funcionario (email, nome_fun, idade, senha, tipo, idCargo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissi", $email, $nome, $idade, $senha, $tipo, $idCargo);

    if ($stmt->execute()) {
        echo "Usuário cadastrado com sucesso.";
    } else {
        echo "Erro ao cadastrar usuário.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Usuário</title>
</head>
<body>

    <h1>Cadastrar Usuário</h1>

    <form method="POST">

        <label>Nome:</label>
        <input type="text" name="nome" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Idade:</label>
        <input type="number" name="idade" required>

        <label>Senha:</label>
        <input type="password" name="senha" required>

        <label>Tipo:</label>
        <select name="tipo" required>
            <option value="Operador">Operador</option>
            <option value="Supervisor">Supervisor</option>
        </select>

        <label>Cargo:</label>
        <select name="idCargo" required>
            <option value="1">Operador</option>
            <option value="2">Supervisor</option>
        </select>

        <button type="submit">Cadastrar</button>

    </form>

</body>
</html>
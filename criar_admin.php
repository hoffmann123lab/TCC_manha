
<?php

require "connection.php";

$email = "admin@sigep.com";
$senha = "Admin@123456";
$nome = "Administrador";
$idade = 30;

$conn->query(
    "INSERT INTO cargo (nome_cargo)
    SELECT 'Operador'
    WHERE NOT EXISTS (
        SELECT 1 FROM cargo WHERE nome_cargo = 'Operador'
    )"
);

$conn->query(
    "INSERT INTO cargo (nome_cargo)
    SELECT 'Supervisor'
    WHERE NOT EXISTS (
        SELECT 1 FROM cargo WHERE nome_cargo = 'Supervisor'
    )"
);

$stmt = $conn->prepare(
    "SELECT id_fun FROM funcionario WHERE email = ?"
);

$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    echo "O administrador já existe.";
    exit;
}

$stmt->close();

$stmt = $conn->prepare(
    "SELECT id_cargo FROM cargo WHERE nome_cargo = 'Supervisor' LIMIT 1"
);

$stmt->execute();
$resultado = $stmt->get_result();
$cargo = $resultado->fetch_assoc();
$stmt->close();

if (!$cargo) {
    exit("Não foi possível encontrar o cargo Supervisor.");
}

$idCargo = (int) $cargo["id_cargo"];
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);
$tipo = "Supervisor";

$stmt = $conn->prepare(
    "INSERT INTO funcionario
    (email, nome_fun, idade, senha, tipo, idCargo)
    VALUES (?, ?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "ssissi",
    $email,
    $nome,
    $idade,
    $senhaHash,
    $tipo,
    $idCargo
);

if ($stmt->execute()) {
    echo "Administrador criado com sucesso!";
    echo "<br>E-mail: " . htmlspecialchars($email, ENT_QUOTES, "UTF-8");
    echo "<br>Senha inicial: " . htmlspecialchars($senha, ENT_QUOTES, "UTF-8");
    echo "<br><br>Apague o arquivo criar_admin.php após a execução.";
} else {
    echo "Erro ao criar administrador.";
}

$stmt->close();
$conn->close();

?>

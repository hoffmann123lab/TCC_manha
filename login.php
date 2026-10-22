
<?php

session_start();

require "connection.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$email = trim($_POST["email"] ?? "");
$senha = $_POST["senha"] ?? "";

if ($email === "" || $senha === "") {
    header("Location: index.php?erro=" . urlencode("Preencha o e-mail e a senha."));
    exit;
}

$stmt = $conn->prepare(
    "SELECT id_fun, nome_fun, email, senha, tipo
    FROM funcionario
    WHERE email = ?
    LIMIT 1"
);

if (!$stmt) {
    header("Location: index.php?erro=" . urlencode("Erro ao processar o login."));
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $usuario = $resultado->fetch_assoc();

    if (password_verify($senha, $usuario["senha"])) {
        session_regenerate_id(true);

        $_SESSION["id_fun"] = (int) $usuario["id_fun"];
        $_SESSION["nome_fun"] = $usuario["nome_fun"];
        $_SESSION["email"] = $usuario["email"];
        $_SESSION["tipo"] = $usuario["tipo"];

        $stmt->close();
        $conn->close();

        header("Location: dashboard.php");
        exit;
    }
}

$stmt->close();
$conn->close();

header("Location: index.php?erro=" . urlencode("E-mail ou senha incorretos."));
exit;

?>

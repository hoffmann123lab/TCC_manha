<?php

require "connection.php";

echo "<h2>Informações da conexão</h2>";

$banco = $conn->query("SELECT DATABASE()")->fetch_row()[0];
$versao = $conn->query("SELECT VERSION()")->fetch_row()[0];

echo "Banco conectado: " . $banco . "<br>";
echo "Versão do MySQL: " . $versao . "<br><br>";

echo "<h3>Tabelas encontradas:</h3>";

$resultado = $conn->query("SHOW TABLES");

if ($resultado->num_rows == 0) {
    echo "NENHUMA TABELA ENCONTRADA.";
} else {
    while ($tabela = $resultado->fetch_row()) {
        echo $tabela[0] . "<br>";
    }
}

?>
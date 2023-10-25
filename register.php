<?php
require_once 'includes/database.php';

// Verifica se o formulário de registro foi submetido
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    // Criptografa a senha usando BCRYPT
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insere os dados no banco de dados
    $stmt = $conn->prepare("INSERT INTO users (username, password, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$username, $password]);

    // Redireciona para a página de login após o registro
    header("Location: login.php");
    exit;
}
?>

<form action="register.php" method="post">
    <label for="username">Username:</label>
    <input type="text" name="username" required>
    <label for="password">Password:</label>
    <input type="password" name="password" required>
    <button type="submit" name="register">Register</button>
</form>

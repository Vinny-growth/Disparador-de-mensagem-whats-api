<?php
require_once 'includes/database.php';
session_start();

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Consulta o banco de dados pelo usuário
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Verifica se o usuário existe e se a senha está correta
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;

        // Redireciona para o dashboard após o login
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Invalid credentials!";
    }
}
?>

<form action="login.php" method="post">
    <label for="username">Username:</label>
    <input type="text" name="username" required>
    <label for="password">Password:</label>
    <input type="password" name="password" required>
    <button type="submit" name="login">Login</button>
</form>

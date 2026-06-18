<?php
session_start();
try {
    $pdo = new PDO('mysql:host=localhost;dbname=messages;charset=utf8mb4', 'root', '');
} catch (PDOException $e) { die("Ошибка: " . $e->getMessage()); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? ''); $password = trim($_POST['password'] ?? '');
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?'); $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id']; $_SESSION['user_name'] = $user['name'];
        header('Location: cabinet.php'); exit;
    } else { $error = 'Неверный логин или пароль.'; }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Вход</title><style>body{font-family:sans-serif;background:#f4f6f9;display:flex;justify-content:center;align-items:center;height:100vh;}.card{background:#fff;padding:30px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.05);width:320px;}.form-group{margin-bottom:15px;display:flex;flex-direction:column;gap:5px;}input,button{padding:10px;border-radius:6px;border:1px solid #cbd5e1;}button{background:#3b82f6;color:#fff;border:none;cursor:pointer;font-weight:bold;}</style></head>
<body>
<div class="card">
    <h2>Вход</h2>
    <?php if($error) echo "<p style='color:red'>$error</p>"; ?>
    <form method="POST">
        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Пароль</label><input type="password" name="password" required></div>
        <button type="submit">Войти</button>
    </form>
    <p style="text-align:center;font-size:14px;"><a href="register.php">Регистрация</a></p>
</div>
</body>
</html>
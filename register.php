<?php
session_start();
try {
    $pdo = new PDO('mysql:host=localhost;dbname=messages;charset=utf8mb4', 'root', '');
} catch (PDOException $e) { die("Ошибка: " . $e->getMessage()); }

$errors = []; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? ''); $password = trim($_POST['password'] ?? '');
    if (strlen($password) < 6) $errors[] = 'Пароль от 6 символов.';
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?'); $stmt->execute([$email]);
        if ($stmt->fetch()) { $errors[] = 'Email занят.'; } else {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $success = 'Успешная регистрация! Войдите.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Регистрация</title><style>body{font-family:sans-serif;background:#f4f6f9;display:flex;justify-content:center;align-items:center;height:100vh;}.card{background:#fff;padding:30px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.05);width:320px;}.form-group{margin-bottom:15px;display:flex;flex-direction:column;gap:5px;}input,button{padding:10px;border-radius:6px;border:1px solid #cbd5e1;}button{background:#3b82f6;color:#fff;border:none;cursor:pointer;font-weight:bold;}</style></head>
<body>
<div class="card">
    <h2>Регистрация</h2>
    <?php if($success) echo "<p style='color:green'>$success</p>"; if(!empty($errors)) echo "<p style='color:red'>".implode('<br>',$errors)."</p>"; ?>
    <form method="POST">
        <div class="form-group"><label>Имя</label><input type="text" name="name" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Пароль</label><input type="password" name="password" required></div>
        <button type="submit">Создать аккаунт</button>
    </form>
    <p style="text-align:center;font-size:14px;"><a href="login.php">Войти</a></p>
</div>
</body>
</html>
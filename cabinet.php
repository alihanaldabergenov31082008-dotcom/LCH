<?php
session_start();

// 1. ЗАЩИТА: Если пользователь не авторизован — отправляем на вход
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 2. ПОДКЛЮЧЕНИЕ К ТВОЕЙ БАЗЕ ДАННЫХ messages
try {
    $pdo = new PDO('mysql:host=localhost;dbname=messages;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

$success = '';
$errors  = [];

// 3. ПОЛУЧЕНИЕ ДАННЫХ ТЕКУЩЕГО ПОЛЬЗОВАТЕЛЯ
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Если сессия активна, но пользователя нет в БД — принудительный разлогин
if (!$user) {
    header('Location: logout.php');
    exit;
}

// 4. ОБРАБОТКА ФОРМЫ РЕДАКТИРОВАНИЯ (ИМЯ И ТЕЛЕФОН)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $name  = trim($_POST['name']  ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name)) {
        $errors[] = 'Имя не может быть пустым.';
    }

    if (!empty($phone) && !preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
        $errors[] = 'Введите корректный номер телефона.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?');
        $stmt->execute([$name, $phone ?: null, $_SESSION['user_id']]);

        $_SESSION['user_name'] = $name;
        $user['name']  = $name;
        $user['phone'] = $phone;

        $success = 'Данные профиля успешно сохранены.';
    }
}

// 5. ОБРАБОТКА ФОРМЫ СМЕНЫ ПАРОЛЯ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_password  = trim($_POST['old_password']  ?? '');
    $new_password  = trim($_POST['new_password']  ?? '');
    $new_password2 = trim($_POST['new_password2'] ?? '');

    if (!password_verify($old_password, $user['password'])) {
        $errors[] = 'Неверный текущий пароль.';
    }

    if (strlen($new_password) < 6) {
        $errors[] = 'Новый пароль — минимум 6 символов.';
    }

    if ($new_password !== $new_password2) {
        $errors[] = 'Новые пароли не совпадают.';
    }

    // ИСПРАВЛЕНО: Теперь переменная $hash передается корректно со знаком $
    if (empty($errors)) {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([$hash, $_SESSION['user_id']]);
        
        $success = 'Пароль успешно изменён.';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; color: #333; padding: 40px; margin: 0; }
        .nav-menu { margin-bottom: 30px; background: #fff; padding: 15px 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); text-align: center; }
        .nav-menu a { margin: 0 15px; color: #3b82f6; text-decoration: none; font-weight: bold; }
        .nav-menu a:hover { text-decoration: underline; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        h1, h2 { color: #1e293b; }
        h2 { font-size: 18px; margin-top: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px; }
        .form-group { margin-bottom: 15px; display: flex; flex-direction: column; gap: 5px; }
        label { font-size: 14px; font-weight: 600; color: #475569; }
        input { padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; }
        input:disabled { background: #e2e8f0; color: #64748b; cursor: not-allowed; }
        button { background: #3b82f6; color: #fff; border: none; padding: 12px; border-radius: 6px; font-size: 15px; font-weight: bold; cursor: pointer; margin-top: 10px; width: 100%; }
        button:hover { background: #2563eb; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; }
        .alert-success { background: #d1e7dd; color: #0f5132; border-left: 4px solid #0f5132; }
        .alert-error { background: #f8d7da; color: #842029; border-left: 4px solid #842029; }
    </style>
</head>
<body>

<div class="nav-menu">
    <a href="index.php">Главная</a>
    <a href="catalog.php">Каталог</a>
    <a href="contact.php">Контакты</a>
    <a href="cabinet.php" style="border-bottom: 2px solid #3b82f6;">Кабинет</a>
    <a href="logout.php" style="color: #ef4444;">Выход</a>
</div>

<div class="container">
    <h1>Личный кабинет</h1>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $e): ?>
                <p style="margin:0;"><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <h2>Мои данные</h2>
        <div class="form-group">
            <label>Email (изменить нельзя)</label>
            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
        </div>
        <div class="form-group">
            <label for="name">Имя</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="phone">Телефон</label>
            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Дата регистрации</label>
            <input type="text" value="<?= date('d.m.Y', strtotime($user['created_at'])) ?>" disabled>
        </div>
        <button type="submit" name="save_profile">Сохранить изменения</button>
    </form>

    <form method="POST" action="">
        <h2>Сменить пароль</h2>
        <div class="form-group">
            <label for="old_password">Текущий пароль</label>
            <input type="password" id="old_password" name="old_password" required>
        </div>
        <div class="form-group">
            <label for="new_password">Новый пароль (от 6 символов)</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>
        <div class="form-group">
            <label for="new_password2">Повторите новый пароль</label>
            <input type="password" id="new_password2" name="new_password2" required>
        </div>
        <button type="submit" name="change_password" style="background: #475569;">Обновить пароль</button>
    </form>
</div>

</body>
</html>
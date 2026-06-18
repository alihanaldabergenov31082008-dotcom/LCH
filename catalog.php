<?php
session_start(); // 
require_once 'config/db.php'; // 

$errors = []; // 
$sent   = false; // 
$name   = ''; // 
$email  = ''; // 
$message_text = ''; // 

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // 
    $name         = trim($_POST['name']    ?? ''); // 
    $email        = trim($_POST['email']   ?? ''); // 
    $message_text = trim($_POST['message'] ?? ''); // 

    // Валидация 
    if (empty($name)) {
        $errors[] = 'Введите ваше имя.'; // 
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { // 
        $errors[] = 'Введите корректный email.'; // 
    }
    if (empty($message_text)) {
        $errors[] = 'Напишите ваше сообщение.'; // 
    } elseif (mb_strlen($message_text) < 10) { // 
        $errors[] = 'Сообщение слишком короткое (минимум 10 символов).'; // 
    }

    // Сохраняем в БД 
    if (empty($errors)) { // 
        $stmt = $pdo->prepare('INSERT INTO messages (name, email, message) VALUES (?, ?, ?)'); // 
        $stmt->execute([$name, $email, $message_text]); // 

        $sent  = true; // 
        $name = $email = $message_text = ''; // 
    }
}
?>
<!DOCTYPE html>
<html lang="ru"> <head>
    <meta charset="UTF-8"> <title>Контакты — CutTime</title> <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 30px; background-color: #f4f6f9; color: #333; }
        h1 { color: #2c3e50; margin-bottom: 25px; font-weight: bold; }
        .nav-menu { margin-bottom: 20px; }
        .nav-menu a { margin-right: 15px; color: #3b82f6; text-decoration: none; font-weight: bold; }
        .container { max-width: 560px; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; display: flex; flex-direction: column; gap: 8px; }
        label { font-size: 14px; font-weight: 600; color: #1e293b; }
        input, textarea { padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; width: 100%; box-sizing: border-box; }
        input:focus, textarea:focus { outline: none; border-color: #3b82f6; }
        button { background-color: #3b82f6; color: white; border: none; padding: 12px; border-radius: 6px; font-size: 16px; cursor: pointer; font-weight: bold; transition: background 0.2s; width: 100%; }
        button:hover { background-color: #2563eb; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .alert-success { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .alert-error { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        .alert p { margin: 5px 0; }
        .btn-link { display: inline-block; text-align: center; background: #64748b; color: #fff; text-decoration: none; padding: 10px; border-radius: 6px; margin-top: 10px; width: 100%; box-sizing: border-box; }
    </style>
</head>
<body>

  <div class="nav-menu">
      <a href="index.php">Главная</a>
      <a href="catalog.php">Услуги</a>
      <a href="contact.php">Контакты</a>
  </div>

  <div class="container">
      <h1 class="section__title">Обратная связь</h1> <?php if ($sent): ?> <div class="alert alert-success"> <p>Ваше сообщение отправлено! Мы свяжемся с вами в ближайшее время.</p> </div>
          <a href="index.php" class="btn-link">На главную</a> <?php else: ?> <?php if (!empty($errors)): ?> <div class="alert alert-error"> <?php foreach ($errors as $e): ?> <p><?= htmlspecialchars($e) ?></p> <?php endforeach; ?> </div> <?php endif; ?> <form method="POST" action=""> <div class="form-group"> <label for="name">Ваше имя *</label> <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="Введите имя" required> </div> <div class="form-group"> <label for="email">Email *</label> <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="ваш@email.com" required> </div> <div class="form-group"> <label for="message">Сообщение *</label> <textarea id="message" name="message" rows="5" placeholder="Ваш вопрос или пожелание..." style="resize:vertical;" required><?= htmlspecialchars($message_text) ?></textarea> </div> <button type="submit">Отправить сообщение</button> </form>
      <?php endif; ?>
  </div>

</body>
</html>
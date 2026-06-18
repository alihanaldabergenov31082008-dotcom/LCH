<?php
session_start(); // Запускаем сессию [cite: 55, 57]

// Подключаемся напрямую к базе данных LCH
try {
    $pdo = new PDO('mysql:host=localhost;dbname=LCH;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

$errors = []; // Массив для ошибок [cite: 55, 57]
$sent   = false; // Флаг успешной отправки [cite: 55, 57]
$name   = '';
$email  = '';
$message_text = '';

// Проверяем отправку формы [cite: 55, 57]
if ($_SERVER['REQUEST_METHOD'] === 'POST') { // [cite: 55, 57]
    $name         = trim($_POST['name']    ?? ''); // [cite: 55, 57]
    $email        = trim($_POST['email']   ?? ''); // [cite: 55, 57]
    $message_text = trim($_POST['message'] ?? ''); // [cite: 55, 57]

    // Валидация полей [cite: 55]
    if (empty($name)) { // [cite: 55]
        $errors[] = 'Введите ваше имя.'; // [cite: 55, 57]
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { // [cite: 55, 57]
        $errors[] = 'Введите корректный email.'; // [cite: 55, 57]
    }
    if (empty($message_text)) { // [cite: 55]
        $errors[] = 'Напишите ваше сообщение.'; // [cite: 55, 57]
    } elseif (mb_strlen($message_text) < 10) { 
        $errors[] = 'Сообщение слишком короткое (минимум 10 символов).'; // [cite: 55, 57]
    }

    // Запись в базу данных LCH, если нет ошибок [cite: 55, 57]
    if (empty($errors)) { // [cite: 55, 57]
        $stmt = $pdo->prepare('INSERT INTO messages (name, email, message) VALUES (?, ?, ?)'); // [cite: 55, 57]
        $stmt->execute([$name, $email, $message_text]); // [cite: 55, 57]

        $sent = true; // [cite: 55, 57]
        $name = $email = $message_text = ''; // [cite: 55, 57]
    }
}
?>
<!DOCTYPE html>
<html lang="ru"> <head>
    <meta charset="UTF-8"> <title>Контакты — ElectroShop</title>
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            margin: 0; 
            padding: 30px; 
            background-color: #f4f6f9; 
            color: #333; 
        }
        .nav-menu { 
            margin-bottom: 30px; 
            background: #fff; 
            padding: 15px 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.05); 
        }
        .nav-menu a { 
            margin-right: 20px; 
            color: #3b82f6; 
            text-decoration: none; 
            font-weight: bold; 
        }
        .nav-menu a:hover { 
            text-decoration: underline; 
        }
        .container { 
            max-width: 560px; 
            margin: 0 auto; 
            background: #fff; 
            padding: 35px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.06); 
        }
        h1 { 
            color: #1e293b; 
            margin-top: 0; 
            margin-bottom: 25px; 
            font-size: 26px; 
        }
        .form-group { 
            margin-bottom: 20px; 
            display: flex; 
            flex-direction: column; 
            gap: 8px; 
        }
        label { 
            font-size: 14px; 
            font-weight: 600; 
            color: #475569; 
        }
        input, textarea { 
            padding: 12px; 
            border: 1px solid #cbd5e1; 
            border-radius: 6px; 
            font-size: 14px; 
            background: #fff; 
            color: #333; 
            width: 100%; 
            box-sizing: border-box; 
        }
        input:focus, textarea:focus { 
            outline: none; 
            border-color: #3b82f6; 
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); 
        }
        button { 
            background-color: #3b82f6; 
            color: white; 
            border: none; 
            padding: 14px; 
            border-radius: 6px; 
            font-size: 16px; 
            cursor: pointer; 
            font-weight: bold; 
            transition: background 0.2s; 
            width: 100%; 
        }
        button:hover { 
            background-color: #2563eb; 
        }
        .alert { 
            padding: 15px; 
            border-radius: 6px; 
            margin-bottom: 20px; 
            font-size: 14px; 
            line-height: 1.5; 
        }
        .alert-success { 
            background-color: #d1e7dd; 
            color: #0f5132; 
            border: 1px solid #badbcc; 
        }
        .alert-error { 
            background-color: #f8d7da; 
            color: #842029; 
            border: 1px solid #f5c2c7; 
        }
        .alert p { 
            margin: 4px 0; 
        }
        .btn-link { 
            display: inline-block; 
            text-align: center; 
            background: #64748b; 
            color: #fff; 
            text-decoration: none; 
            padding: 12px; 
            border-radius: 6px; 
            margin-top: 10px; 
            width: 100%; 
            box-sizing: border-box; 
            font-weight: bold; 
        }
        .btn-link:hover { 
            background: #475569; 
        }
    </style>
</head>
<body>

  <div class="nav-menu">
      <a href="index.php">Главная</a>
      <a href="catalog.php">Товары</a>
      <a href="contact.php">Контакты</a>
  </div>

  <div class="container">
      <h1>Обратная связь</h1> <?php if ($sent): ?> <div class="alert alert-success"> <p>Ваше сообщение успешно отправлено! Мы свяжемся с вами в ближайшее время.</p> </div>
          <a href="index.php" class="btn-link">На главную</a> <?php else: ?> <?php if (!empty($errors)): ?> <div class="alert alert-error"> <?php foreach ($errors as $e): ?> <p><?= htmlspecialchars($e) ?></p> <?php endforeach; ?> </div> <?php endif; ?> <form method="POST" action=""> <div class="form-group"> <label for="name">Ваше имя *</label> <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="Введите имя" required> </div> <div class="form-group"> <label for="email">Email *</label> <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="ваш@email.com" required> </div> <div class="form-group"> <label for="message">Сообщение *</label> <textarea id="message" name="message" rows="5" placeholder="Ваш вопрос или пожелание..." style="resize:vertical;" required><?= htmlspecialchars($message_text) ?></textarea> </div> <button type="submit">Отправить сообщение</button> </form>
          
      <?php endif; ?>
  </div>

</body>
</html>
<?php
session_start(); // Запускаем сессию для работы формы [cite: 17, 19]

// ПРЯМОЕ ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ (Исправляет ошибку со скриншота)
try {
    // Подключаемся к базе cuttime, как написано в твоей методичке
    $pdo = new PDO('mysql:host=localhost;dbname=cuttime;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

$errors = []; // Массив для ошибок [cite: 17, 19]
$sent   = false; // Флаг успешной отправки [cite: 17, 19]
$name   = '';
$email  = '';
$message_text = '';

// Проверяем, была ли отправлена форма методом POST [cite: 17, 19]
if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $name         = trim($_POST['name']    ?? ''); // Убираем лишние пробелы [cite: 17, 19]
    $email        = trim($_POST['email']   ?? ''); 
    $message_text = trim($_POST['message'] ?? ''); 

    // Валидация полей [cite: 17, 19]
    if (empty($name)) {
        $errors[] = 'Введите ваше имя.'; [cite: 17, 19]
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { 
        $errors[] = 'Введите корректный email.'; [cite: 17, 19]
    }
    if (empty($message_text)) {
        $errors[] = 'Напишите ваше сообщение.'; [cite: 17, 19]
    } elseif (mb_strlen($message_text) < 10) { // Используем mb_strlen для поддержки кириллицы
        $errors[] = 'Сообщение слишком короткое (минимум 10 символов).'; [cite: 17]
    }

    // Если ошибок нет — сохраняем данные в таблицу messages [cite: 17, 19]
    if (empty($errors)) { 
        $stmt = $pdo->prepare('INSERT INTO messages (name, email, message) VALUES (?, ?, ?)'); [cite: 17, 19]
        $stmt->execute([$name, $email, $message_text]); [cite: 17, 19]

        $sent = true; // Меняем флаг, чтобы показать экран успешной отправки [cite: 17, 19]

        // Очищаем переменные, чтобы поля формы сбросились [cite: 17, 19]
        $name = $email = $message_text = ''; [cite: 17, 19]
    }
}
?>
<!DOCTYPE html>
<html lang="ru"> [cite: 21]
<head>
    <meta charset="UTF-8"> [cite: 21]
    <title>Контакты — CutTime</title> [cite: 21]
    <style>
        /* Современные встроенные стили, чтобы страница выглядела красиво без внешних CSS файлов */
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
            box-shadow: 0 4px 15px rgba(0,0,0,0.06); [cite: 21]
        }
        h1 { 
            color: #1e293b; 
            margin-top: 0; 
            margin-bottom: 25px; 
            font-size: 26px; [cite: 21]
        }
        .form-group { 
            margin-bottom: 20px; 
            display: flex; 
            flex-direction: column; 
            gap: 8px; [cite: 21]
        }
        label { 
            font-size: 14px; 
            font-weight: 600; 
            color: #475569; [cite: 21]
        }
        input, textarea { 
            padding: 12px; 
            border: 1px solid #cbd5e1; 
            border-radius: 6px; 
            font-size: 14px; 
            background: #fff; 
            color: #333; 
            width: 100%; 
            box-sizing: border-box; [cite: 21]
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
            width: 100%; [cite: 21]
        }
        button:hover { 
            background-color: #2563eb; 
        }
        .alert { 
            padding: 15px; 
            border-radius: 6px; 
            margin-bottom: 20px; 
            font-size: 14px; 
            line-height: 1.5; [cite: 21]
        }
        .alert-success { 
            background-color: #d1e7dd; 
            color: #0f5132; 
            border: 1px solid #badbcc; [cite: 21]
        }
        .alert-error { 
            background-color: #f8d7da; 
            color: #842029; 
            border: 1px solid #f5c2c7; [cite: 21]
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
      [cite_start]<a href="index.php">Главная</a> [cite: 25]
      [cite_start]<a href="catalog.php">Услуги</a> [cite: 25]
      [cite_start]<a href="contact.php">Контакты</a> [cite: 25]
  </div>

  <div class="container">
      [cite_start]<h1>Обратная связь</h1> [cite: 21]

      [cite_start]<?php if ($sent): ?> [cite: 21, 36]
          [cite_start]<div class="alert alert-success"> [cite: 21]
              <p>Ваше сообщение отправлено! [cite_start]Мы свяжемся с вами в ближайшее время.</p> [cite: 21]
          </div>
          [cite_start]<a href="index.php" class="btn-link">На главную</a> [cite: 21]

      [cite_start]<?php else: ?> [cite: 21, 36]

          [cite_start]<?php if (!empty($errors)): ?> [cite: 21]
              [cite_start]<div class="alert alert-error"> [cite: 21]
                  [cite_start]<?php foreach ($errors as $e): ?> [cite: 21]
                      [cite_start]<p><?= htmlspecialchars($e) ?></p> [cite: 21]
                  <?php endforeach; [cite_start]?> [cite: 21]
              [cite_start]</div> [cite: 21]
          <?php endif; [cite_start]?> [cite: 21]

          [cite_start]<form method="POST" action=""> [cite: 21]
              [cite_start]<div class="form-group"> [cite: 21]
                  [cite_start]<label for="name">Ваше имя *</label> [cite: 21]
                  [cite_start]<input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="Введите имя" required> [cite: 21]
              [cite_start]</div> [cite: 21]

              [cite_start]<div class="form-group"> [cite: 21]
                  [cite_start]<label for="email">Email *</label> [cite: 21]
                  [cite_start]<input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="ваш@email.com" required> [cite: 21]
              [cite_start]</div> [cite: 21]

              [cite_start]<div class="form-group"> [cite: 21]
                  [cite_start]<label for="message">Сообщение *</label> [cite: 21]
                  [cite_start]<textarea id="message" name="message" rows="5" placeholder="Ваш вопрос или пожелание..." style="resize:vertical;" required><?= htmlspecialchars($message_text) ?></textarea> [cite: 21, 36]
              [cite_start]</div> [cite: 21]

              [cite_start]<button type="submit">Отправить сообщение</button> [cite: 21]
          </form>
          
      <?php endif; ?>
  </div>

</body>
</html>
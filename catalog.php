<?php
session_start();
require_once 'config/db.php';

// 1. ЧИТАЕМ ФИЛЬТРЫ ИЗ GET-ПАРАМЕТРОВ
$search    = trim($_GET['search']    ?? '');
$max_price = intval($_GET['max_price'] ?? 0);
$sort      = $_GET['sort'] ?? 'id';

// Защита: разрешаем только известные значения сортировки
$allowed_sort = ['id', 'price_asc', 'price_desc', 'name'];
if (!in_array($sort, $allowed_sort)) {
    $sort = 'id';
}

// 2. ДИНАМИЧЕСКИЙ SQL-ЗАПРОС
$where  = [];
$params = [];

// Если введён поиск — добавляем условие
if (!empty($search)) {
    $where[]  = 'name LIKE ?';
    $params[] = '%' . $search . '%';
}

// Если задана максимальная цена — добавляем условие
if ($max_price > 0) {
    $where[]  = 'price <= ?';
    $params[] = $max_price;
}

// Собираем WHERE из массива условий
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Настраиваем сортировку
$order_sql = match($sort) {
    'price_asc'  => 'ORDER BY price ASC',
    'price_desc' => 'ORDER BY price DESC',
    'name'       => 'ORDER BY name ASC',
    default      => 'ORDER BY id ASC',
};

// Выполняем итоговый запрос к базе данных (замените services на вашу таблицу, если нужно)
$stmt = $pdo->prepare("SELECT * FROM services $where_sql $order_sql");
$stmt->execute($params);
$services = $stmt->fetchAll();
$count    = count($services);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Каталог услуг</title>
    </head>
<body>

  <form method="GET" action="">
    
    <div class="filter-group">
      <label for="search">Поиск по названию</label>
      <input type="text" id="search" name="search" 
             value="<?= htmlspecialchars($search) ?>" 
             placeholder="Например: стрижка">
    </div>

    <div class="filter-group
<?php
session_start();

// ИМИТАЦИЯ БАЗЫ ДАННЫХ (Список электроники прямо в коде)
$all_products = [
    ['id' => 1, 'name' => 'Смартфон FruitPhone 15', 'description' => '128 ГБ, черный, отличная камера и яркий экран.', 'price' => 450000],
    ['id' => 2, 'name' => 'Ноутбук MacroBook Air 13', 'description' => 'Процессор M2, 8 ГБ ОЗУ, 256 ГБ SSD, тонкий корпус.', 'price' => 620000],
    ['id' => 3, 'name' => 'Беспроводные наушники SoundBlast', 'description' => 'Активное шумоподавление, до 30 часов работы.', 'price' => 45000],
    ['id' => 4, 'name' => 'Умные часы FitTrack 5', 'description' => 'Пульсометр, шагомер, защита от воды, экран AMOLED.', 'price' => 85000],
    ['id' => 5, 'name' => 'Игровая приставка GameBox X', 'description' => 'Поддержка 4K, 1 ТБ памяти, в комплекте 1 геймпад.', 'price' => 290000],
];

// 1. ПОЛУЧАЕМ ФИЛЬТРЫ ИЗ СТРОКИ БРАУЗЕРА
$search    = trim($_GET['search']    ?? '');
$max_price = intval($_GET['max_price'] ?? 0);
$sort      = $_GET['sort'] ?? 'id';

// 2. ФИЛЬТРУЕМ НАШ СПИСОК ТОВАРОВ
$products = [];
foreach ($all_products as $p) {
    // Фильтр по поиску
    if (!empty($search) && mb_stripos($p['name'], $search) === false) {
        continue;
    }
    // Фильтр по максимальной цене
    if ($max_price > 0 && $p['price'] > $max_price) {
        continue;
    }
    $products[] = $p;
}

// 3. СОРТИРУЕМ РЕЗУЛЬТАТЫ
usort($products, function($a, $b) use ($sort) {
    return match($sort) {
        'price_asc'  => $a['price'] <=> $b['price'],
        'price_desc' => $b['price'] <=> $a['price'],
        'name'       => strnatcmp($a['name'], $b['name']),
        default      => $a['id'] <=> $b['id'],
    };
});

$count = count($products);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Магазин электроники</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 30px; background-color: #f4f6f9; color: #333; }
        h1 { color: #2c3e50; }
        form { margin-bottom: 30px; display: flex; gap: 15px; align-items: flex-end; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .filter-group { display: flex; flex-direction: column; gap: 5px; flex-grow: 1; }
        input, select, button { padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; }
        input:focus, select:focus { outline: none; border-color: #3b82f6; }
        button { background-color: #3b82f6; color: white; border: none; cursor: pointer; font-weight: bold; transition: background 0.2s; }
        button:hover { background-color: #2563eb; }
        .cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-top: 20px; }
        .card { background: white; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s; }
        .card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px rgba(0,0,0,0.05); }
        .card h3 { margin-top: 0; color: #1e293b; font-size: 18px; }
        .card p { color: #64748b; font-size: 14px; line-height: 1.5; margin: 10px 0; }
        .price { font-weight: bold; color: #10b981; font-size: 1.3em; margin-bottom: 0; }
        .reset-link { display: inline-block; margin-bottom: 12px; color: #64748b; text-decoration: none; border-bottom: 1px dashed; }
    </style>
</head>
<body>

  <h1>ElectroShop — Магазин электроники</h1>

  <form method="GET" action="">
    
    <div class="filter-group">
      <label for="search">Поиск товара</label>
      <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Например: Смартфон">
    </div>

    <div class="filter-group">
      <label for="max_price">Максимальная цена (₸)</label>
      <input type="text" id="max_price" name="max_price" value="<?= $max_price > 0 ? $max_price : '' ?>" placeholder="500000">
    </div>

    <div class="filter-group">
      <label for="sort">Сортировка</label>
      <select id="sort" name="sort">
        <option value="id" <?= $sort === 'id' ? 'selected' : '' ?>>По умолчанию</option>
        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Сначала дешевые</option>
        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Сначала дорогие</option>
        <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>По названию (А-Я)</option>
      </select>
    </div>

    <button type="submit">Найти</button>

    <?php if ($search || $max_price || $sort !== 'id'): ?>
      <a href="catalog.php" class="reset-link">Сбросить</a>
    <?php endif; ?>

  </form>

  <div class="catalog-
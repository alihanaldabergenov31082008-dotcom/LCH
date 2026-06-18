<?php
session_start();

// ИМИТАЦИЯ БАЗЫ ДАННЫХ (Просто список услуг прямо в коде)
$all_services = [
    ['id' => 1, 'name' => 'Мужская стрижка', 'description' => 'Классическая стрижка ножницами и машинкой.', 'price' => 4000],
    ['id' => 2, 'name' => 'Женская стрижка', 'description' => 'Стрижка любой сложности, мытье головы и легкая укладка.', 'price' => 6000],
    ['id' => 3, 'name' => 'Окрашивание волос', 'description' => 'Современные техники окрашивания премиум материалами.', 'price' => 15000],
    ['id' => 4, 'name' => 'Оформление бороды', 'description' => 'Стрижка бороды и усов, четкие контуры и уход с маслом.', 'price' => 3000],
    ['id' => 5, 'name' => 'Детская стрижка', 'description' => 'Аккуратная стрижка для самых маленьких (до 12 лет).', 'price' => 2500],
];

// 1. ПОЛУЧАЕМ ФИЛЬТРЫ ИЗ СТРОКИ БРАУЗЕРА
$search    = trim($_GET['search']    ?? '');
$max_price = intval($_GET['max_price'] ?? 0);
$sort      = $_GET['sort'] ?? 'id';

// 2. ФИЛЬТРУЕМ НАШ СПИСОК
$services = [];
foreach ($all_services as $s) {
    // Фильтр по поиску (ищем совпадение букв без учета регистра)
    if (!empty($search) && mb_stripos($s['name'], $search) === false) {
        continue;
    }
    // Фильтр по максимальной цене
    if ($max_price > 0 && $s['price'] > $max_price) {
        continue;
    }
    $services[] = $s;
}

// 3. СОРТИРУЕМ РЕЗУЛЬТАТ
usort($services, function($a, $b) use ($sort) {
    return match($sort) {
        'price_asc'  => $a['price'] <=> $b['price'],
        'price_desc' => $b['price'] <=> $a['price'],
        'name'       => strnatcmp($a['name'], $b['name']),
        default      => $a['id'] <=> $b['id'],
    };
});

$count = count($services);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Каталог услуг</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f9f9f9; }
        form { margin-bottom: 30px; display: flex; gap: 15px; align-items: flex-end; background: #fff; padding: 15px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .filter-group { display: flex; flex-direction: column; gap: 5px; }
        input, select, button { padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        button { background-color: #e67e22; color: white; border: none; cursor: pointer; font-weight: bold; }
        button:hover { background-color: #d35400; }
        .cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-top: 20px; }
        .card { background: white; border: 1px solid #ddd; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between; }
        .card h3 { margin-top: 0; color: #2c3e50; }
        .card p { color: #7f8c8d; font-size: 14px; line-height: 1.4; }
        .price { font-weight: bold; color: #27ae60; font-size: 1.2em; margin-bottom: 0; }
        .reset-link { display: inline-block; margin-bottom: 10px; color: #7f8c8d; }
    </style>
</head>
<body>

  <h1>Каталог услуг Салона</h1>

  <form method="GET" action="">
    
    <div class="filter-group">
      <label for="search">Поиск по названию</label>
      <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Например: стрижка">
    </div>

    <div class="filter-group">
      <label for="max_price">Максимальная цена (₸)</label>
      <input type="text" id="max_price" name="max_price" value="<?= $max_price > 0 ? $max_price : '' ?>" placeholder="5000">
    </div>

    <div class="filter-group">
      <label for="sort">Сортировка</label>
      <select id="sort" name="sort">
        <option value="id" <?= $sort === 'id' ? 'selected' : '' ?>>По умолчанию</option>
        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Цена: от меньшей</option>
        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Цена: от большей</option>
        <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>По названию</option>
      </select>
    </div>

    <button type="submit">Применить</button>

    <?php if ($search || $max_price || $sort !== 'id'): ?>
      <a href="catalog.php" class="reset-link">Сбросить</a>
    <?php endif; ?>

  </form>

  <div class="catalog-results">
    <h2>Результаты поиска (Найдено: <?= $count ?>)</h2>

    <?php if (empty($services)): ?>
      <p>Ничего не найдено по вашему запросу. <a href="catalog.php">Показать все услуги</a></p>
    <?php else: ?>
      <div class="cards-grid">
        <?php foreach ($services as $s): ?>
          <div class="card">
            <div>
              <h3><?= htmlspecialchars($s['name']) ?></h3>
              <p><?= htmlspecialchars($s['description']) ?></p>
            </div>
            <p class="price"><?= number_format($s['price'], 0, '.', ' ') ?> ₸</p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</body>
</html>
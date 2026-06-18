<?php
session_start(); [cite: 21]
require_once 'config/db.php'; [cite: 21]

// Читаем фильтры из GET-параметров
// ?? '' означает: если параметра нет — используем пустую строку
$search    = trim($_GET['search']    ?? ''); [cite: 21]
$max_price = intval($_GET['max_price'] ?? 0); [cite: 21]
$sort      = $_GET['sort'] ?? 'id'; [cite: 21]

// Защита: разрешаем только известные значения сортировки
$allowed_sort = ['id', 'price_asc', 'price_desc', 'name']; [cite: 21]
if (!in_array($sort, $allowed_sort)) $sort = 'id'; [cite: 21]

// Массивы для условий WHERE и параметров
$where  = []; [cite: 25]
$params = []; [cite: 25]

// Если введён поиск — добавляем условие
if (!empty($search)) { [cite: 25]
    $where[]  = 'name LIKE ?'; [cite: 25]
    $params[] = '%' . $search . '%'; [cite: 25]
} [cite: 25]

// Если задана максимальная цена — добавляем условие
if ($max_price > 0) { [cite: 25]
    $where[]  = 'price <= ?'; [cite: 25]
    $params[] = $max_price; [cite: 25]
} [cite: 25]

// Собираем WHERE из массива условий
// Если условий нет — $where_sql будет пустой строкой
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : ''; [cite: 25]

// Сортировка
$order_sql = match($sort) { [cite: 25]
    'price_asc'  => 'ORDER BY price ASC', [cite: 25]
    'price_desc' => 'ORDER BY price DESC', [cite: 25]
    'name'       => 'ORDER BY name ASC', [cite: 25]
    default      => 'ORDER BY id ASC', [cite: 25]
}; [cite: 25]

// Итоговый запрос
$stmt = $pdo->prepare("SELECT * FROM services $where_sql $order_sql"); [cite: 25]
$stmt->execute($params); [cite: 25]
$services = $stmt->fetchAll(); [cite: 25]
$count    = count($services); [cite: 25]
?>

<form method="GET" action=""> [cite: 29]

  <div class="filter-group"> [cite: 29]
    [cite_start]<label for="search">Поиск по названию</label> [cite: 29]
    [cite_start]<input type="text" id="search" name="search" [cite: 29]
      [cite_start]value="<?= htmlspecialchars($search) ?>" [cite: 29]
      [cite_start]placeholder="Например: стрижка"> [cite: 29]
  [cite_start]</div> [cite: 29]

  [cite_start]<div class="filter-group"> [cite: 29]
    [cite_start]<label for="max_price">Максимальная цена (₸)</label> [cite: 29]
    [cite_start]<input type="text" id="max_price" name="max_price" [cite: 29]
      [cite_start]value="<?= $max_price > 0 ? $max_price : '' ?>"> [cite: 29]
  [cite_start]</div> [cite: 29]

  [cite_start]<div class="filter-group"> [cite: 29]
    [cite_start]<label for="sort">Сортировка</label> [cite: 29]
    [cite_start]<select id="sort" name="sort"> [cite: 29]
      <option value="id" <?= $sort==='id' ? [cite_start]'selected' : '' ?>>По умолчанию</option> [cite: 29]
      <option value="price_asc" <?= $sort==='price_asc' ? [cite_start]'selected' : '' ?>>Цена ↑</option> [cite: 29]
      <option value="price_desc" <?= $sort==='price_desc' ? [cite_start]'selected' : '' ?>>Цена ↓</option> [cite: 29]
      <option value="name" <?= $sort==='name' ? [cite_start]'selected' : '' ?>>По названию</option> [cite: 29]
    [cite_start]</select> [cite: 29]
  [cite_start]</div> [cite: 29]

  [cite_start]<button type="submit">Применить</button> [cite: 29]

  [cite_start]<?php if ($search || $max_price): ?> [cite: 29]
    [cite_start]<a href="catalog.php">Сбросить фильтры</a> [cite: 29]
  <?php endif; [cite_start]?> [cite: 29]

[cite_start]</form> [cite: 29]

[cite_start]<div class="catalog-results"> [cite: 32]

  [cite_start]<div class="catalog-results__header"> [cite: 32]
    [cite_start]<h2> [cite: 32]
      [cite_start]<?php if ($search || $max_price): ?> [cite: 32]
        [cite_start]Результаты поиска [cite: 32]
      [cite_start]<?php else: ?> [cite: 32]
        [cite_start]Все услуги [cite: 32]
      <?php endif; [cite_start]?> [cite: 32]
    [cite_start]</h2> [cite: 32]
    [cite_start]<span>Найдено: <?= $count ?></span> [cite: 32]
  [cite_start]</div> [cite: 32]

  [cite_start]<?php if (empty($services)): ?> [cite: 32]
    [cite_start]<p>По вашему запросу ничего не найдено. [cite: 32]
       [cite_start]<a href="catalog.php">Сбросить фильтры</a> [cite: 32]
    [cite_start]</p> [cite: 32]

  [cite_start]<?php else: ?> [cite: 32]
    [cite_start]<div class="cards-grid"> [cite: 32]
      [cite_start]<?php foreach ($services as $s): ?> [cite: 32]
        [cite_start]<div class="card"> [cite: 32]
          [cite_start]<div class="card__body"> [cite: 32]
            [cite_start]<h3><?= htmlspecialchars($s['name']) ?></h3> [cite: 32]
            [cite_start]<p><?= htmlspecialchars($s['description']) ?></p> [cite: 32]
            [cite_start]<p><?= number_format($s['price'],0,'.',' ') ?> ₸</p> [cite: 32]
          [cite_start]</div> [cite: 32]
        [cite_start]</div> [cite: 32]
      <?php endforeach; [cite_start]?> [cite: 32]
    [cite_start]</div> [cite: 32]
  <?php endif; [cite_start]?> [cite: 32]

[cite_start]</div> [cite: 32]
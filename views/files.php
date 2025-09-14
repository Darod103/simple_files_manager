<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= TITLE ?> </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="container">
    <h1 class="mb-4"><?= TITLE ?> </h1>

    <!-- Хлебные крошки -->
    <nav aria-label="breadcrumb" class="breadcrumbs">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="?">Home</a></li>
            <?php foreach (($breadcrumbs ?? []) as $bc): ?>
                <li class="breadcrumb-item">
                    <a href="<?= htmlspecialchars($bc['url'] ?? '#', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                        <?= htmlspecialchars($bc['name'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>

    <!-- Кнопка Вверх -->
    <?php if (!empty($breadcrumbs) && !empty($upUrl)): ?>
        <p class="up-link"><a href="<?= htmlspecialchars($upUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="btn btn-secondary">⬆ Вверх</a></p>
    <?php endif; ?>

    <div class="row row-cols-1 row-cols-md-4 g-4">
        <?php foreach (($items ?? []) as $f): ?>
            <div class="col">
                <div class="card h-100 folder-item">
                    <div class="card-body d-flex flex-column justify-content-between align-items-center">
                        <?php if ($f['type'] === 'dir'): ?>
                            <a href="<?= htmlspecialchars($f['url'] ?? '#', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="d-flex flex-column align-items-center text-decoration-none text-dark">
                                <span class="icon">📁</span>
                                <span class="mt-2">
                                    <?= htmlspecialchars($f['name'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </span>
                            </a>
                        <?php else: ?>
                            <a href="<?= htmlspecialchars(str_replace('?r=stream', '?r=viewImage', $f['src']) ?? '#', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="d-flex flex-column align-items-center text-decoration-none text-dark">
                                <img class="thumb" loading="lazy" src="<?= htmlspecialchars($f['src'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" alt="<?= htmlspecialchars($f['name'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                <span><?= htmlspecialchars($f['name'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>

<?php
$pages = [
    'avaliacao' => 'Avaliar aplicativo',
    'suporte' => 'Ajuda e suporte',
    'faq' => 'Perguntas frequentes',
    'sobre' => 'Sobre o sistema',
];
$shellTitle = $pages[$p];
$shellName = $account['nome'];
ob_start();
require __DIR__ . '/dashboard_header.php';
?>
<main class="dashboard-content profile-content">
    <nav class="profile-links" aria-label="Ajuda e avaliação">
        <?php foreach ($pages as $route => $label): ?>
            <a href="?p=<?= e($route) ?>" <?= $p === $route ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
        <?php endforeach; ?>
    </nav>
    <section class="account-detail" aria-label="<?= e($shellTitle) ?>"><?= $pageContent ?></section>
</main>
</div>
</div>
<?php view($shellTitle, ob_get_clean());

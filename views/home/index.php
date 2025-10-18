<?php 
// データベースから取得した作品配列 $works が $data['works'] に格納されています。
$works = $data['works'];
$currentPage = $data['currentPage'];
$totalPages = $data['totalPages'];
// ★修正: HomeControllerから渡されるキー currentSiteId を使用し、変数名も currentSiteId に統一
$currentSiteId = $data['currentSiteId']; 
?>

<div class="container mt-4">
    <h1 class="text-white mb-4">最新作品一覧</h1>
    
    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-3">
        <?php foreach ($works as $work): ?>
            <?php 
            // _work_card.php は $work 変数を必要とします
            require __DIR__ . '/../components/_work_card.php'; 
            ?>
            <?php endforeach; ?>
    </div>

    <?php 
    // _pagination.php は $currentPage, $totalPages, $currentSiteId を使用します
    require __DIR__ . '/../components/_pagination.php'; 
    ?>
</div>
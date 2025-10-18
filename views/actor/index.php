<div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-3">
    <?php 

    // dd($works);
    // $works配列が存在し、空ではない場合にループを実行
    if (!empty($works)): 
    ?>
        <?php foreach ($works as $work): ?>
            <?php require __DIR__ . '/../components/_work_card.php'; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <p class="alert alert-warning">該当する作品は見つかりませんでした。</p>
        </div>
    <?php endif; ?>
</div>

<?php
// ★ 修正点: 以下の手動設定行を削除しました ★
// $isTagPage = true; 

// ページネーションコンポーネントを読み込む
// Controllerから渡された $currentPage, $totalPages, $siteId,
// および $isTagPage/$tagNameUrl または $isSeriesPage/$seriesNameUrl または $isActorPage/$actorNameUrl が利用されます。
require __DIR__ . '/../components/_pagination.php';
?>
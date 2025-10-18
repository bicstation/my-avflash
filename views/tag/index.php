<div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-3">
    <?php 
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
// タグ別ページであることを示す変数を設定
$isTagPage = true; 

// TagControllerから渡されたページネーションに必要な変数:
// $currentPage, $totalPages, $siteId, $tagNameUrl, $pagination 

// ページネーションコンポーネントを読み込む
require __DIR__ . '/../components/_pagination.php';
?>
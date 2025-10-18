<?php 
// WorkControllerから渡されたデータ
$work = $data['work'];
$siteId = $data['siteId'];
$sidebarData = $data['sidebarData'];
dump($work);
?>

<div class="container mt-4">
    <div class="row">
        
        <div class="col-md-12">
            <h1 class="text-white mb-4"><?= htmlspecialchars($work['title'] ?? '作品タイトル') ?></h1>
            <div class="card bg-dark text-white border-secondary mb-4">
                <div class="row g-0">
                    <div class="col-md-4">
                        <img src="<?= htmlspecialchars($work['cover_url'] ?? '') ?>" class="img-fluid rounded-start" alt="<?= htmlspecialchars($work['title'] ?? '') ?>">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <p class="card-text small text-muted">作品ID: <?= htmlspecialchars($work['work_id'] ?? 'N/A') ?></p>
                            <p class="card-text small text-info">配信日: <?= htmlspecialchars(date('Y/m/d', strtotime($work['release_date'] ?? 'now'))) ?></p>
                            <hr class="border-secondary">
                            
                            <p><?= nl2br(htmlspecialchars($work['comment'] ?? '作品の概要がここに表示されます。')) ?></p>
                            
                            <div class="d-grid gap-2 mt-4">
                                <?= $work['link_title'] ?? '' ?> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <h2 class="text-white mb-3">作品イメージ</h2>
            <div class="row g-3 mb-5">
                <?php foreach (['capture_1_link', 'capture_2_link', 'capture_3_link'] as $key): ?>
                    <?php if (!empty($work[$key])): ?>
                    <div class="col-md-4">
                        <img src="<?= htmlspecialchars($work[$key]) ?>" class="img-fluid rounded shadow" alt="キャプチャ画像" loading="lazy">
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            
        </div>
        

    </div>
</div>
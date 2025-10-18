<?php
/**
 * 単一の作品カードコンポーネント
 * @var array $work データベースから取得した単一の作品データ
 * @var string $tagName 親ビューから渡される現在のタグ名 (今回追加)
 */

// このコンポーネントは、親ビューで $work 変数がセットされていることを前提とする
if (!isset($work) || !is_array($work)) {
    return;
}

// サイトIDを取得（worksテーブルのsite_idカラムを想定）
$siteId = $work['site_id'] ?? 'default';
// 作品IDを取得
$workId = $work['work_id'] ?? '';

// ★★★ 修正箇所 ★★★
// 作品詳細URLの新しい形式（/work/siteId-workId）を定義
$detailUrl = "/work/" . htmlspecialchars($siteId) . "-" . htmlspecialchars($workId);
// ★★★ 修正箇所ここまで ★★★


// ★修正1: siteIdが'all'の場合、URLに /all を含めないためのプレフィックスを定義
// カテゴリ別リンク (manufacturer/actor) 用
$pathPrefix = ($siteId === 'all') ? '' : "/" . htmlspecialchars($siteId);

// ★追加: 親ビューからタグ名が渡されていない場合は空文字を設定 (安全対策)
$tagName = $tagName ?? ''; 
?>

<div class="col">
    <div class="card bg-dark border-secondary h-100 work-card">
        
        <a href="<?= $detailUrl ?>" title="<?= htmlspecialchars($work['title'] ?? '') ?>">
            <img src="<?= htmlspecialchars($work['cover_url'] ?? '') ?>" class="card-img-top" alt="<?= htmlspecialchars($work['title'] ?? '') ?>" loading="lazy">
        </a>
        
        <div class="card-body p-2">
            <p class="card-text text-muted small mb-1">ID: <?= htmlspecialchars($workId) ?></p>
            
            <h5 class="card-title work-title-link mb-2">
                <a href="<?= $detailUrl ?>" class="text-white" title="<?= htmlspecialchars($work['title'] ?? '') ?>">
                    <?= htmlspecialchars(mb_strimwidth($work['title'] ?? '', 0, 50, '...', 'UTF-8')) ?>
                </a>
            </h5>
            
            <p class="text-info small mt-1 mb-0">
                <?php 
                $brandName = $work['brand_name'] ?? null;
                if ($brandName) {
                    $urlEncodedBrandName = urlencode($brandName);
                    // ★修正2: $pathPrefixを使用してブランドリンクを生成 (e.g., /lemon/manufacturer/xxx または /manufacturer/xxx)
                    $brandLink = "{$pathPrefix}/manufacturer/{$urlEncodedBrandName}";

                    echo '<a href="' . htmlspecialchars($brandLink) . '" class="text-info" title="' . htmlspecialchars($brandName) . '">';
                    echo htmlspecialchars($brandName);
                    echo '</a>';
                } else {
                    echo "不明";
                }
                ?>
            </p>
            
            <p class="text-muted small mb-0">
                <?php
                if ($siteId !== 'default') {
                    $siteNameDisplay = strtoupper($siteId); 
                    // サイトホームへのリンク (siteId='all'の場合は /、それ以外は /{siteId})
                    $siteHomeLink = ($siteId === 'all') ? '/' : '/' . htmlspecialchars($siteId);

                    echo 'サイト: ';
                    echo '<a href="' . htmlspecialchars($siteHomeLink) . '" class="text-muted" title="' . htmlspecialchars($siteNameDisplay) . 'の作品一覧">';
                    echo htmlspecialchars($siteNameDisplay);
                    echo '</a>';
                }
                ?>
            </p>

            <p class="text-danger small mb-0 actor-names">
                <?php 
                $actorNamesString = $work['actor_names'] ?? '';

                if (!empty($actorNamesString)) {
                    $actorNames = array_map('trim', explode(',', $actorNamesString));
                    $links = [];
                    
                    foreach ($actorNames as $name) {
                        if (empty($name)) continue;

                        $urlEncodedName = urlencode($name);
                        // ★修正3: $pathPrefixを使用して俳優リンクを生成 (e.g., /lemon/actor/xxx または /actor/xxx)
                        $actorLink = "{$pathPrefix}/actor/{$urlEncodedName}";
                        
                        $links[] = '<a href="' . htmlspecialchars($actorLink) . '" class="text-danger" title="' . htmlspecialchars($name) . '">' . htmlspecialchars($name) . '</a>';
                    }
                    
                    echo implode(', ', $links);
                } else {
                    echo '不明'; 
                }
                ?>
            </p>
            <p class="text-info small mt-1 mb-0">
                <?php 
                $releaseDate = $work['release_date'] ?? null;
                if ($releaseDate) {
                    echo htmlspecialchars(date('Y/m/d', strtotime($releaseDate)));
                } else {
                    echo "日付不明";
                }
                ?>
            </p>
        </div>
        
        <div class="card-footer bg-secondary p-0 border-0">
            <?= $work['link_title'] ?? '' ?>
        </div>
        
        <?php if (!empty($tagName)): ?>
        <div class="card-footer bg-dark p-2 border-top border-secondary">
            <p class="text-white small mb-0">
                <i class="fas fa-tag me-1"></i> <?= htmlspecialchars($tagName) ?>
            </p>
        </div>
        <?php endif; ?>
        
    </div>
</div>
<?php
/**
 * ファイル名: sidebar_integrated.php
 * 想定されるデータ: 
 * $sidebarData = [...]; 
 * $sidebarMeta = [ 'sidebarTitle' => '...', 'staticCategories' => [...] ];
 * $siteId: コントローラから渡された現在のサイトID ('lemon', 'okashi', 'b10f')
 */

// ★★★ 修正済み: 変数の安全装置を追加し、未定義エラーを防止する ★★★
$siteId = $siteId ?? null;
$sidebarData = $sidebarData ?? []; // ★ 追加: $sidebarDataが未定義の場合、空配列で初期化
$sidebarMeta = $sidebarMeta ?? []; // ★ 追加: $sidebarMetaが未定義の場合、空配列で初期化

// $siteId が未定義の場合、'okashi'を安全なデフォルトとする
$currentSiteId = $siteId ?? 'okashi'; 

// NavControllerから渡された $sidebarMeta の値を使用し、ローカル変数を定義
$sidebarTitle = $sidebarMeta['sidebarTitle'] ?? '人気コンテンツ';
$staticCategories = $sidebarMeta['staticCategories'] ?? [];

// URLパス生成ロジック
$sitePathPrefix = '/' . htmlspecialchars($currentSiteId);
?>

<div class="sidebar p-3 bg-white border rounded shadow-sm">

    <h5 class="py-2 border-bottom mb-3 text-primary">
        <i class="fas fa-bars me-2"></i><?= htmlspecialchars($sidebarTitle) ?>
    </h5>

    <div class="sidebar-section mb-4">
        <h6 class="text-secondary fw-bold mb-2"><i class="fas fa-layer-group me-1"></i>サイト切り替え</h6>
        <ul class="nav flex-column mb-3">
            
            <li class="nav-item">
                <a class="nav-link text-dark ps-0 py-1 <?= ($currentSiteId === 'lemon') ? 'active fw-bold text-warning' : ''; ?>" 
                    href="/lemon">
                    <i class="fas fa-lemon me-2 text-warning"></i>LEMON（レモン）
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark ps-0 py-1 <?= ($currentSiteId === 'okashi') ? 'active fw-bold text-danger' : ''; ?>" 
                    href="/okashi">
                    <i class="fas fa-candy-cane me-2 text-danger"></i>OKASHI（お菓子）
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-dark ps-0 py-1 <?= ($currentSiteId === 'b10f') ? 'active fw-bold text-info' : ''; ?>" 
                    href="/b10f">
                    <i class="fas fa-building me-2 text-info"></i>B10F（地下１０階）
                </a>
            </li>
            <hr class="my-2 w-75">
            
            <h6 class="text-secondary fw-bold mt-3 mb-2"><i class="fas fa-list me-1"></i>ジャンル別ナビ</h6>
            
            <li class="nav-item"><a class="nav-link text-dark ps-0 py-1" href="<?= $sitePathPrefix ?>/category/new"><i class="fas fa-fire me-2 text-danger"></i>最新作品</a></li>
            <li class="nav-item"><a class="nav-link text-dark ps-0 py-1" href="<?= $sitePathPrefix ?>/category/ranking"><i class="fas fa-star me-2 text-warning"></i>人気ランキング</a></li>
            
            <?php foreach ($staticCategories as $cat): ?>
                <li class="nav-item">
                    <a class="nav-link text-dark ps-0 py-1" href="<?= htmlspecialchars($cat['href']) ?>">
                        <i class="<?= htmlspecialchars($cat['icon']) ?> me-2 text-info"></i><?= htmlspecialchars($cat['name']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <hr class="my-3">

    <div class="sidebar-section mb-4">
        <h6 class="text-secondary fw-bold mb-3"><i class="fas fa-industry me-1"></i>人気メーカー（ブランド）</h6>
        <?php if (!empty($sidebarData['brands'])): ?>
            <ul class="list-group list-group-flush manufacturer-list small">
                <?php foreach ($sidebarData['brands'] as $brand): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-1">
                        <a href="<?= $sitePathPrefix ?>/brand/<?= urlencode($brand['name']) ?>" class="text-dark text-decoration-none">
                            <?= htmlspecialchars($brand['name']) ?>
                        </a>
                        <span class="badge bg-secondary-subtle text-secondary">
                            <?= number_format($brand['work_count']) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted small">関連するメーカーはありません。</p>
        <?php endif; ?>
    </div>

    <hr class="my-3">

    <div class="sidebar-section mb-4">
        <h6 class="text-secondary fw-bold mb-3"><i class="fas fa-bookmark me-1"></i>人気レーベル</h6>
        <?php if (!empty($sidebarData['labels'])): ?>
            <ul class="list-group list-group-flush label-list small">
                <?php foreach ($sidebarData['labels'] as $label): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-1">
                        <a href="<?= $sitePathPrefix ?>/label/<?= urlencode($label['name']) ?>" class="text-dark text-decoration-none">
                            <?= htmlspecialchars($label['name']) ?>
                        </a>
                        <span class="badge bg-secondary-subtle text-secondary">
                            <?= number_format($label['work_count']) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted small">関連するレーベルはありません。</p>
        <?php endif; ?>
    </div>

    <hr class="my-3">

    <div class="sidebar-section mb-4">
        <h6 class="text-secondary fw-bold mb-3"><i class="fas fa-tags me-1"></i>人気タグ</h6>
        <?php if (!empty($sidebarData['tags'])): ?>
            <div class="d-flex flex-wrap tag-list">
                <?php foreach ($sidebarData['tags'] as $tag): ?>
                    <a href="<?= $sitePathPrefix ?>/tag/<?= urlencode($tag['name']) ?>"
                        class="badge text-decoration-none p-1 px-2 m-1 bg-light text-dark border small">
                        <?= htmlspecialchars($tag['name']) ?>
                        <span class="text-muted">(<?= number_format($tag['work_count']) ?>)</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted small">関連するタグはありません。</p>
        <?php endif; ?>
    </div>

    <hr class="my-3">

    <div class="sidebar-section mb-4">
        <h6 class="text-secondary fw-bold mb-3"><i class="fas fa-video me-1"></i>人気シリーズ</h6>
        <?php if (!empty($sidebarData['series'])): ?>
            <ul class="list-group list-group-flush series-list small">
                <?php foreach ($sidebarData['series'] as $series): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-1">
                        <a href="<?= $sitePathPrefix ?>/series/<?= urlencode($series['name']) ?>" class="text-dark text-decoration-none">
                            <?= htmlspecialchars($series['name']) ?>
                        </a>
                        <span class="badge bg-secondary-subtle text-secondary">
                            <?= number_format($series['work_count']) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted small">関連するシリーズはありません。</p>
        <?php endif; ?>
    </div>

    <hr class="my-3">

    <div class="sidebar-section mb-4">
        <h6 class="text-secondary fw-bold mb-3"><i class="fas fa-user-tag me-1"></i>人気の出演者</h6>
        <?php if (!empty($sidebarData['actors'])): ?>
            <ul class="list-group list-group-flush actor-list small">
                <?php foreach ($sidebarData['actors'] as $actor): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-1">
                        <a href="<?= $sitePathPrefix ?>/actor/<?= urlencode($actor['name']) ?>" class="text-dark text-decoration-none">
                            <?= htmlspecialchars($actor['name']) ?>
                        </a>
                        <span class="badge bg-secondary-subtle text-secondary">
                            <?= number_format($actor['work_count']) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted small">関連する出演者はいません。</p>
        <?php endif; ?>
    </div>

    <hr class="my-3">
    <div class="sidebar-section">
        <h6 class="text-secondary fw-bold mb-3"><i class="fas fa-link me-1"></i>関連リンク</h6>
        <p class="small text-muted border p-2 bg-light">ここにASP広告や重要な内部リンクを配置します。</p>
    </div>
</div>
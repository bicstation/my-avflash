<?php
/**
 * ページネーションコンポーネント
 * 汎用性を高めるため、コントローラー側で以下の変数を view() 関数に渡す必要があります。
 * - $currentPage: 現在のページ番号 (必須)
 * - $totalPages: 総ページ数 (必須)
 * - $siteId または $currentSiteId: 現在のサイトID (例: 'lemon', 'all')
 * - $tagNameUrl, $actorNameUrl, etc.: ページネーション対象のエンティティ名 (例: タグ名)
 */

// ----------------------------------------------------------------------
// ★修正箇所: 変数の安全な初期化とパスの決定
// ----------------------------------------------------------------------

// コントローラーから渡される可能性のある変数名を統一して初期化
// $siteIdが渡されるケースが多いため、それを優先し、どちらもなければ空文字とする
$siteId = $siteId ?? $currentSiteId ?? '';
$tagNameUrl = $tagNameUrl ?? '';
$actorNameUrl = $actorNameUrl ?? '';
$manufacturerNameUrl = $manufacturerNameUrl ?? '';
$seriesNameUrl = $seriesNameUrl ?? '';

// ページングの基準となるベースルートを決定
if (!empty($tagNameUrl)) {
    // タグページ: /tag/{tagName}
    $baseRoute = "/tag/" . htmlspecialchars($tagNameUrl);
} elseif (!empty($actorNameUrl)) {
    // アクターページ: /actor/{actorName}
    $baseRoute = "/actor/" . htmlspecialchars($actorNameUrl);
} elseif (!empty($manufacturerNameUrl)) {
    // メーカーページ: /manufacturer/{manufacturerName}
    $baseRoute = "/manufacturer/" . htmlspecialchars($manufacturerNameUrl);
} elseif (!empty($seriesNameUrl)) {
    // シリーズページ: /series/{seriesName}
    $baseRoute = "/series/" . htmlspecialchars($seriesNameUrl);
} else {
    // ホームページまたはその他のページ
    $baseRoute = "";
}

// サイトIDのプレフィックスを決定（'all' や空の場合はプレフィックスなし）
// SiteIDが 'all' の場合や、ルート(/)の場合、URLにはサイトIDを含めない
$sitePrefix = ($siteId === 'all' || empty($siteId) || $baseRoute === "") ? '' : "/" . htmlspecialchars($siteId);
$baseLink = $sitePrefix . $baseRoute;

// 現在のページと総ページ数の必須チェック
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;

if ($totalPages <= 1) {
    return;
}

// ページネーションに表示するページ数の範囲を決定 (例: 現在のページから前後2ページ)
$startPage = max(1, $currentPage - 2);
$endPage = min($totalPages, $currentPage + 2);

if ($startPage > 1) {
    $startPage = max(1, $startPage - 1);
}
if ($endPage < $totalPages) {
    $endPage = min($totalPages, $endPage + 1);
}
?>

<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center mt-3">
        
        <li class="page-item <?= $currentPage === 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= htmlspecialchars($baseLink) ?>?page=1" aria-label="First">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        
        <li class="page-item <?= $currentPage === 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= htmlspecialchars($baseLink) ?>?page=<?= max(1, $currentPage - 1) ?>" aria-label="Previous">
                <span aria-hidden="true">&lt;</span>
            </a>
        </li>
        
        <?php if ($startPage > 1): ?>
            <li class="page-item disabled"><span class="page-link">...</span></li>
        <?php endif; ?>

        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                <a class="page-link" href="<?= htmlspecialchars($baseLink) ?>?page=<?= $i ?>">
                    <?= $i ?>
                </a>
            </li>
        <?php endfor; ?>
        
        <?php if ($endPage < $totalPages): ?>
            <li class="page-item disabled"><span class="page-link">...</span></li>
        <?php endif; ?>

        <li class="page-item <?= $currentPage === $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= htmlspecialchars($baseLink) ?>?page=<?= min($totalPages, $currentPage + 1) ?>" aria-label="Next">
                <span aria-hidden="true">&gt;</span>
            </a>
        </li>

        <li class="page-item <?= $currentPage === $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= htmlspecialchars($baseLink) ?>?page=<?= $totalPages ?>" aria-label="Last">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>

    </ul>
</nav>
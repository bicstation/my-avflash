<?php 
/**
 * テンプレートファイル: views/works/index.php
 * 変数 $works はコントローラーから view() ヘルパーを通して展開されています。
 */
?>
<div>
    <h3>作品リスト</h3>
    <?php if (empty($works)): ?>
        <p>現在、作品がありません。CSVファイル（/data/以下）と config.php のパス設定を確認してください。</p>
    <?php else: ?>
        <ul>
            <?php foreach ($works as $work): ?>
                <li>
                    <a href="<?php echo htmlspecialchars($work['商品URL']); ?>" target="_blank">
                        [<?php echo htmlspecialchars($work['商品ID']); ?>] 
                        <strong><?php echo htmlspecialchars($work['タイトル']); ?></strong>
                        (ブランド: <?php echo htmlspecialchars($work['ブランド']); ?> / 価格: <?php echo $work['レンタル視聴を含む最低価格[税込]']; ?>円)
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
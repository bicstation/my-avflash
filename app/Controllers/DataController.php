<?php

namespace App\Controllers;

use App\Core\Database;
use App\Models\WorksModel;

class DataController
{
    private WorksModel $worksModel;

    /**
     * CSVのカラム名マッピング。サイトIDに応じて異なるCSVヘッダーに対応する。
     */
    private const CSV_COLUMN_MAP = [
        // OKASHI の長いカラム名
        'okashi' => [
            'cover_url'           => '広告用画像URL(他に1.jpg[ジャケット表大]、2.jpg[ジャケット裏大]、c1.jpgからcキャプチャ画像数.jpg[キャプチャ画像]も利用できます)',
            'jacket_link'         => 'どれでもバナー ジャケット表(小)型',
            'player_html'         => 'どれでもバナー プレーヤ型(iframe)型',
            'brand_link'          => 'どれでもバナー メーカ作品一覧',
            'label_link'          => 'どれでもバナー レーベル作品一覧', // レーベルのリンク
            'series_link'         => 'どれでもバナー シリーズ作品一覧', // シリーズのリンク
            'raw_manufacturer_name' => 'ブランド', // 生のメーカー名カラム (実質ブランド名)
            'raw_series_name'     => 'シリーズ',
        ],
        // LEMON の短縮されたカラム名
        'lemon' => [
            // ★ 作品情報
            'cover_url'           => '広告用ジャケットURL',
            'jacket_link'         => 'どれでもバナー ジャケット型',
            'player_html'         => 'どれでもバナー プレーヤ型(iframe)型',

            // ★ リレーション情報
            'raw_manufacturer_name' => 'ブランド',
            'brand_link'          => 'どれでもバナー メーカ作品一覧',
            'label_link'          => 'どれでもバナー レーベル作品一覧',
            'series_link'         => 'どれでもバナー シリーズ作品一覧',

            // ★ 紐付け情報
            'actor_link'          => 'どれでもバナー 出演作品一覧',
            'tag_link'            => 'どれでもバナー タグ作品一覧',

            // ★ キャプチャ画像 (c1〜c16)
            'capture_1'           => 'どれでもバナー キャプチャ1',
            'capture_2'           => 'どれでもバナー キャプチャ2',
            'capture_3'           => 'どれでもバナー キャプチャ3',
            'capture_4'           => 'どれでもバナー キャプチャ4',
            'capture_5'           => 'どれでもバナー キャプチャ5',
            'capture_6'           => 'どれでもバナー キャプチャ6',
            'capture_7'           => 'どれでもバナー キャプチャ7',
            'capture_8'           => 'どれでもバナー キャプチャ8',
            'capture_9'           => 'どれでもバナー キャプチャ9',
            'capture_10'          => 'どれでもバナー キャプチャ10',
            'capture_11'          => 'どれでもバナー キャプチャ11',
            'capture_12'          => 'どれでもバナー キャプチャ12',
            'capture_13'          => 'どれでもバナー キャプチャ13',
            'capture_14'          => 'どれでもバナー キャプチャ14',
            'capture_15'          => 'どれでもバナー キャプチャ15',
            'capture_16'          => 'どれでもバナー キャプチャ16',
        ],
        // B10F のカラム名
        'b10f' => [
            // ★ 作品情報
            'cover_url'           => '広告用画像URL(他に1.jpg[ジャケット表大]、2.jpg[ジャケット裏大]、c1.jpgからcキャプチャ画像数.jpg[キャプチャ画像]も利用できます)',
            'jacket_link'         => 'どれでもバナー ジャケット表(小)型',
            'player_html'         => 'どれでもバナー プレーヤ型(iframe)型',

            // ★ リレーション情報
            'raw_manufacturer_name' => 'ブランド',
            'brand_link'          => 'どれでもバナー メーカ作品一覧',
            'series_link'         => 'どれでもバナー シリーズ作品一覧',

            // B10Fにはレーベルリンクが存在しないため、空文字列を設定
            'label_link'          => '',

            'actor_link'          => 'どれでもバナー 出演作品一覧',
            'tag_link'            => 'どれでもバナー タグ作品一覧',

            // ★ キャプチャ画像 (c1〜c16)
            'capture_1'           => 'どれでもバナー キャプチャ1',
            'capture_2'           => 'どれでもバナー キャプチャ2',
            'capture_3'           => 'どれでもバナー キャプチャ3',
            'capture_4'           => 'どれでもバナー キャプチャ4',
            'capture_5'           => 'どれでもバナー キャプチャ5',
            'capture_6'           => 'どれでもバナー キャプチャ6',
            'capture_7'           => 'どれでもバナー キャプチャ7',
            'capture_8'           => 'どれでもバナー キャプチャ8',
            'capture_9'           => 'どれでもバナー キャプチャ9',
            'capture_10'          => 'どれでもバナー キャプチャ10',
            'capture_11'          => 'どれでもバナー キャプチャ11',
            'capture_12'          => 'どれでもバナー キャプチャ12',
            'capture_13'          => 'どれでもバナー キャプチャ13',
            'capture_14'          => 'どれでもバナー キャプチャ14',
            'capture_15'          => 'どれでもバナー キャプチャ15',
            'capture_16'          => 'どれでもバナー キャプチャ16',
        ],
    ];

    public function __construct(WorksModel $worksModel)
    {
        $this->worksModel = $worksModel;
    }

    /**
     * CSVファイルを読み込み、データベースにインポートする処理の実行
     * @param string $csvFilePath CSVファイルのパス
     */
    public function importData(string $csvFilePath): void
    {
        // CSVファイルパスからサイトIDを抽出するロジック
        $pathInfo = pathinfo($csvFilePath);
        // ファイル名（例: 'b10f.csv' から 'b10f'）をサイトIDとして使用
        $siteId = $pathInfo['filename'] ?? ''; 

        if (!in_array($siteId, ['okashi', 'lemon', 'b10f'])) {
            // siteIdが不正、またはファイル名から特定できなかった場合
            echo "Error: Invalid site ID provided or could not be inferred from file path: " . $csvFilePath . "\n";
            return;
        }

        if (!file_exists($csvFilePath)) {
            echo "Error: CSV file not found at " . $csvFilePath . "\n";
            return;
        }

        // CSVファイルを開く
        $file = fopen($csvFilePath, 'r');
        if ($file === false) {
            echo "Error: Could not open CSV file.\n";
            return;
        }

        // ヘッダーを読み込み
        $headers = fgetcsv($file);
        if ($headers === false) {
            echo "Error: Could not read CSV headers.\n";
            fclose($file);
            return;
        }

        // ヘッダーの整形とマップの取得
        $headers = array_map(function ($h) {
            return trim($h);
        }, $headers);
        $map = self::CSV_COLUMN_MAP[$siteId];
        $worksCount = 0;

        try {
            // トランザクション開始
            Database::getConnection()->beginTransaction();
            
            while (($row = fgetcsv($file)) !== false) {
                // 行データとヘッダーを組み合わせる (連想配列化)
                if (count($headers) !== count($row)) {
                    // データとヘッダーの列数が一致しない場合はスキップ
                    error_log("Skipping row due to column count mismatch.");
                    continue;
                }
                $rowData = array_combine($headers, $row);

                // マップから生のブランド名カラム名を取得
                $rawBrandNameCol = $map['raw_manufacturer_name'];

                // 基本作品情報の抽出
                $workId         = $rowData['商品ID'] ?? null;
                $releaseDate    = $rowData['配信日'] ?? null;
                $title          = $rowData['タイトル'] ?? null;
                $brandNameRaw   = $rowData[$rawBrandNameCol] ?? null; // ★ マップされたカラム名を使用
                $categoryName   = $rowData['カテゴリー名'] ?? null;
                $price          = (int) ($rowData['レンタル視聴を含む最低価格[税込]'] ?? 0);
                $runtimeMin     = (int) ($rowData['収録時間'] ?? 0);
                $comment        = $rowData['コメント'] ?? null;
                $captureCount   = (int) ($rowData['キャプチャ画像数'] ?? 0);
                $productUrl     = $rowData['商品URL'] ?? null;

                if (empty($workId) || empty($releaseDate) || empty($title) || empty($productUrl)) {
                    continue; // 必須データがなければスキップ
                }

                // サイトごとのヘッダー名を持つ共通データの抽出
                $coverUrlCol = $map['cover_url'];
                $jacketLinkCol = $map['jacket_link'];
                $playerHtmlCol = $map['player_html'];
                $brandLinkCol = $map['brand_link'];

                $coverUrl       = $rowData[$coverUrlCol] ?? null;
                $jacketLinkHtml = $rowData[$jacketLinkCol] ?? null;
                $playerHtml     = $rowData[$playerHtmlCol] ?? null;
                $brandLinkHtml  = $rowData[$brandLinkCol] ?? null;

                // 'どれでもバナー'形式で共通しているヘッダーから抽出
                $actorLinkHtml  = $rowData['どれでもバナー 出演作品一覧'] ?? '';
                $tagLinkHtml    = $rowData['どれでもバナー タグ作品一覧'] ?? '';
                
                // --- データのクリーニングと抽出 ---

                // 女優名抽出
                $actorName = $this->extractActorNameFromHtml($actorLinkHtml);
                // タグ名抽出
                $tagNames = $this->extractTagsFromHtml($tagLinkHtml);

                // --- worksテーブルへの挿入/更新 (最優先) ---
                $worksData = [
                    'site_id'       => $siteId,
                    'work_id'       => $workId,
                    'release_date'  => $releaseDate,
                    'title'         => $title,
                    'brand_name'    => $brandNameRaw,
                    'category_name' => $categoryName,
                    'price'         => $price,
                    'runtime_min'   => $runtimeMin,
                    'comment'       => $comment,
                    'capture_count' => $captureCount,
                    'cover_url'     => $coverUrl,
                    'product_url'   => $productUrl,
                    'player_html'   => $playerHtml,
                ];
                // worksテーブルに挿入（works_idが確定する）
                $this->worksModel->insertOrUpdateWork($worksData);
                $worksCount++;

                // ★ 追加: 100件ごとの進捗表示ロジック ★
                if ($worksCount % 100 === 0) {
                    echo "Processing record #{$worksCount}...\n";
                }


                // --- 関連テーブルへの挿入/更新 (worksテーブル参照ありのものをworks挿入直後に移動) ---

                // 1. キャプチャ画像の挿入 (work_captures) ★ 移動
                $captureLinks = [];
                if ($captureCount > 0) {
                    // マップ定義に沿って最大16件のキャプチャリンクをチェック
                    for ($i = 1; $i <= 16; $i++) {
                        $key = "capture_{$i}";
                        if (!isset($map[$key])) continue;

                        $headerName = $map[$key];
                        $captureLinkHtml = $rowData[$headerName] ?? '';
                        if (!empty($captureLinkHtml)) {
                            // リンクHTMLから画像URLを抽出
                            $imageUrl = '';
                            if (preg_match('/src="([^"]+)"/i', $captureLinkHtml, $matches)) {
                                $imageUrl = $matches[1];
                            }

                            $captureLinks[] = [
                                'capture_number' => $i,
                                'image_url'      => $imageUrl,
                                'link_html'      => $captureLinkHtml,
                            ];
                        }
                    }

                    if (!empty($captureLinks)) {
                        $this->worksModel->insertCaptures($workId, $siteId, $captureLinks);
                    }
                }

                // 2. アフィリエイトリンクの挿入 (work_affiliates) ★ 移動
                $affiliateData = [
                    'link_jacket'       => $rowData['どれでもバナー ジャケット表型'] ?? '',
                    'link_player'       => $rowData['どれでもバナー プレーヤ型(iframe)型'] ?? '',
                    'link_title'        => $rowData['どれでもバナー 作品名リンク'] ?? '',
                    'link_actor_list'   => $rowData['どれでもバナー 出演作品一覧'] ?? '',
                    'link_series_list'  => $rowData['どれでもバナー シリーズ作品一覧'] ?? '',
                ];
                // キャプチャリンクを work_affiliates 用の配列に追加 (最大16件)
                for ($i = 1; $i <= 16; $i++) {
                    $headerKey = "どれでもバナー キャプチャ{$i}";
                    $columnKey = "capture_{$i}_link";

                    if (isset($rowData[$headerKey])) {
                        $affiliateData[$columnKey] = $rowData[$headerKey];
                    }
                }

                // Modelを呼び出して work_affiliates に挿入
                $this->worksModel->insertOrUpdateWorkAffiliates($workId, $siteId, $affiliateData);


                // --- 関連テーブルへの挿入/更新 (worksテーブル参照なし/あっても遅延可能) ---

                // 3. アクターの挿入と関連付け (work_actors)
                if (!empty($actorName)) {
                    $actorId = $this->worksModel->insertOrGetActor($actorName, $siteId);
                    $this->worksModel->insertWorkActorLink($workId, $siteId, $actorId);
                }

                // 4. タグの挿入と関連付け (work_tags)
                if (!empty($tagNames)) {
                    $this->worksModel->insertWorkTags($workId, $siteId, $tagNames);
                }

                // 5. ブランドの挿入と関連付け (brands, work_brands)
                if (!empty($brandNameRaw) && !empty($brandLinkHtml)) {
                    $brandId = $this->worksModel->insertOrGetBrand($brandNameRaw, $siteId);
                    if ($brandId > 0) {
                        $this->worksModel->insertWorkBrandLink($workId, $siteId, $brandId, $brandLinkHtml);
                    }
                }

                // 6. レーベルの挿入と関連付け (labels, work_labels)
                $labelLinkHtml = $rowData[$map['label_link']] ?? ''; // B10Fでは空文字列になる
                $labelNameRaw = $this->extractLabelNameFromHtml($labelLinkHtml);
                if (!empty($labelNameRaw) && !empty($labelLinkHtml)) {
                    $labelId = $this->worksModel->insertOrGetLabel($labelNameRaw, $siteId);
                    if ($labelId > 0) {
                        $this->worksModel->insertWorkLabelLink($workId, $siteId, $labelId, $labelLinkHtml);
                    }
                }

                // 7. シリーズの挿入と関連付け (series, work_series)
                $seriesLinkHtml = $rowData[$map['series_link']] ?? '';
                $seriesNameRaw = $this->extractSeriesNameFromHtml($seriesLinkHtml);

                if (!empty($seriesNameRaw) && !empty($seriesLinkHtml)) {
                    $seriesId = $this->worksModel->insertOrGetSeries($seriesNameRaw, $siteId);
                    if ($seriesId > 0) {
                        $this->worksModel->insertWorkSeriesLink($workId, $siteId, $seriesId, $seriesLinkHtml);
                    }
                }
            }

            // トランザクションコミット
            Database::getConnection()->commit();
            
            // ===============================================================
            // ★★★ 診断ロジックの追加 (変更なし) ★★★
            // ===============================================================

            echo "\n--- Import Completed: {$worksCount} records processed ---\n";
            
            // --- 1. 再計算前のカウント表示 ---
            // ※ WorksModel.phpにgetTableCounts()メソッドが実装されている必要があります
            $preRecalculationCounts = $this->worksModel->getTableCounts();
            echo "--- Table Counts (Pre-Recalculation) ---\n";
            foreach ($preRecalculationCounts as $table => $count) {
                echo "{$table}: {$count}\n";
            }
            echo "-----------------------------------------\n";

            // カウントの再計算
            $this->worksModel->recalculateCounts();
            echo "Successfully ran recalculateCounts().\n";

            $this->worksModel->recalculateLabelCounts();
            echo "Successfully ran recalculateLabelCounts().\n";

            $this->worksModel->recalculateBrandCounts();
            echo "Successfully ran recalculateBrandCounts().\n";

            $this->worksModel->recalculateSeriesCounts();
            echo "Successfully ran recalculateSeriesCounts().\n";
            
            // --- 2. 再計算後のカウント表示 ---
            $postRecalculationCounts = $this->worksModel->getTableCounts();
            echo "\n--- Table Counts (Post-Recalculation) ---\n";
            foreach ($postRecalculationCounts as $table => $count) {
                echo "{$table}: {$count}\n";
            }
            echo "-------------------------------------------\n";
            echo "Successfully imported {$worksCount} records for {$siteId}.\n";

        } catch (\PDOException $e) {
            // エラーが発生した場合、ロールバック
            if (Database::getConnection()->inTransaction()) {
                 Database::getConnection()->rollBack();
            }
            echo "Database error during import: " . $e->getMessage() . "\n";

        } finally {
            fclose($file);
        }
    }

    // --- データ抽出ヘルパーメソッド ---

    /**
     * CSVのHTML文字列からタグ名を抽出する
     * @param string $tagHtml CSVから読み込んだ「タグ作品一覧」カラムのHTML文字列
     * @return array クリーンなタグ名の配列
     */
    private function extractTagsFromHtml(string $tagHtml): array
    {
        $tags = [];
        // <a>タグのリンクテキストをすべて抽出する
        preg_match_all('/<a[^>]*>(.*?)<\/a>/i', $tagHtml, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $tagText) {
                // 抽出したテキストから不要な記号や空白を除去する
                $cleanedTag = trim(
                    str_replace(['[', ']'], '', $tagText)
                );
                if (!empty($cleanedTag)) {
                    // タグを「/」で区切って複数ある場合は分割する (例: フルHD対応 / 私服)
                    $splitTags = array_map('trim', explode('/', $cleanedTag));
                    $tags = array_merge($tags, $splitTags);
                }
            }
        }
        // 重複を排除して返す
        return array_unique(array_filter($tags));
    }

    /**
     * CSVのHTML文字列から女優名を抽出する
     * @param string $actorLinkHtml CSVから読み込んだ「出演作品一覧」カラムのHTML文字列
     * @return string クリーンな女優名
     */
    private function extractActorNameFromHtml(string $actorLinkHtml): string
    {
        $actorText = '';
        // 1. <a>タグのリンクテキストを抽出する
        if (preg_match('/<a[^>]*>(.*?)<\/a>/i', $actorLinkHtml, $matches)) {
            $actorText = $matches[1] ?? '';
        }
        // 2. 空白をトリム
        $actorText = trim($actorText);
        // 3. (ローマ字表記など) の部分があれば除去する
        $parenPos = strpos($actorText, '(');
        if ($parenPos !== false) {
            // '(' の前の部分のみを抽出
            $actorText = trim(substr($actorText, 0, $parenPos));
        }
        // 4. 空白でない女優名を返す
        return $actorText;
    }


    /**
     * CSVのHTML文字列からレーベル名を抽出する
     * @param string $labelLinkHtml CSVから読み込んだ「レーベル作品一覧」カラムのHTML文字列
     * @return string クリーンなレーベル名
     */
    private function extractLabelNameFromHtml(string $labelLinkHtml): string
    {
        $labelText = '';
        // 1. <a>タグのリンクテキストを抽出する
        if (preg_match('/<a[^>]*>(.*?)<\\/a>/i', $labelLinkHtml, $matches)) {
            $labelText = $matches[1] ?? '';
        }
        // 2. 抽出したテキストをクリーンアップ
        return trim($labelText);
    }

    /**
     * CSVのHTML文字列からシリーズ名を抽出する
     * @param string $seriesLinkHtml CSVから読み込んだ「シリーズ作品一覧」カラムのHTML文字列
     * @return string クリーンなシリーズ名
     */
    private function extractSeriesNameFromHtml(string $seriesLinkHtml): string
    {
        $seriesText = '';
        // 1. <a>タグのリンクテキストを抽出する
        if (preg_match('/<a[^>]*>(.*?)<\\/a>/i', $seriesLinkHtml, $matches)) {
            $seriesText = $matches[1] ?? '';
        }
        // 2. 抽出したテキストをクリーンアップ
        return trim($seriesText);
    }
}
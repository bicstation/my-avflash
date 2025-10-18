<?php
// /home/wp552476/avflash.xyz/public_html/app/Models/WorksModel.php

namespace App\Models;

use App\Core\Database;

class WorksModel
{
    /** @var array 全作品データを格納する配列 (CSV処理用) */
    private array $data = [];

    /** @var array CSVヘッダー（列名）の正確なリスト */
    private array $headers = [
        '商品ID',
        '配信日',
        'タイトル',
        'キャプチャ画像数',
        '広告画像タイプ',
        '広告用ジャケットURL',
        '商品URL',
        'コメント',
        'レンタル視聴を含む最低価格[税込]',
        '収録時間',
        'ブランド',
        'カテゴリー名',
        '出演',
        'どれでもバナー ジャケット型',
        'どれでもバナー プレーヤ型(iframe)型',
        'どれでもバナー 作品名リンク',
        'どれでもバナー 出演作品一覧',
        'どれでもバナー メーカ作品一覧',
        'どれでもバナー レーベル作品一覧',
        'どれでもバナー シリーズ作品一覧',
        'どれでもバナー タグ作品一覧',
        'どれでもバナー キャプチャ1',
        'どれでもバナー キャプチャ2',
        'どれでもバナー キャプチャ3',
        'どれでもバナー キャプチャ4',
        'どれでもバナー キャプチャ5',
        'どれでもバナー キャプチャ6',
        'どれでもバナー キャプチャ7',
        'どれでもバナー キャプチャ8',
        'どれでもバナー キャプチャ9',
        'どれでもバナー キャプチャ10',
        'どれでもバナー キャプチャ11',
        'どれでもバナー キャプチャ12',
        'どれでもバナー キャプチャ13',
        'どれでもバナー キャプチャ14',
        'どれでもバナー キャプチャ15',
        'どれでもバナー キャプチャ16'
    ];

    /** @var int CSVヘッダーの列数 */
    private const FIELD_COUNT = 37;

    // ====================================================================
    // ★★★ CSV連携メソッド (3つのCSVに対応するロジック) ★★★
    // ====================================================================

    /**
     * config.phpで定義されたすべてのCSVファイルを読み込み、データを統合する
     * 3つのCSV（またはそれ以上）に対応するコアロジックです。
     */
    public function loadCsvData(array $csvFiles): void
    {
        $allData = [];
        foreach ($csvFiles as $label => $path) {
            if (!file_exists($path)) {
                error_log("Warning: CSV file not found for label '{$label}' at {$path}");
                continue;
            }

            if (($handle = fopen($path, 'r')) !== false) {
                // 1. ヘッダー行をスキップ
                if (fgetcsv($handle, 0, ',') === false) {
                    fclose($handle);
                    continue;
                }

                // 2. データ行の処理
                while (($dataRow = fgetcsv($handle, 0, ',')) !== false) {
                    if (count($dataRow) !== self::FIELD_COUNT) {
                        error_log("Skipping row in {$label}.csv due to field count mismatch. Expected: " . self::FIELD_COUNT . ", Actual: " . count($dataRow));
                        continue;
                    }

                    $rowData = array_combine($this->headers, $dataRow);
                    $workId = $rowData['商品ID'] ?? null;

                    if ($workId) {
                        // 商品IDをキーとしてデータを統合（後続のCSVで上書き可能）
                        $allData[$workId] = $rowData;
                    }
                }
                fclose($handle);
            }
        }
        $this->data = $allData;
    }

    /**
     * CSVデータから最新の作品を取得する (DBが使用できない場合の代替)
     */
    public function getLatestWorksFromCsv(int $limit): array
    {
        // loadCsvData() が実行されている前提
        // 配列のキーを維持しつつ、先頭から指定数だけ切り出す
        return array_slice($this->data, 0, $limit, true);
    }

    // ====================================================================
    // ★★★ コア作品取得メソッド (サイト別/全サイト) ★★★
    // ====================================================================

    /**
     * 特定のサイトIDに紐づく最新の作品を取得する
     */
    public function getLatestWorks(string $siteId, int $limit, int $offset = 0): array
    {
        $sql = "SELECT w.*, wa.link_title, wa.capture_1_link
                FROM works w
                JOIN work_affiliates wa ON w.work_id = wa.work_id AND wa.site_id = w.site_id
                WHERE w.site_id = :siteId  -- サイトIDで絞り込み
                ORDER BY w.release_date DESC, w.id DESC 
                LIMIT :limit OFFSET :offset";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':siteId', $siteId, \PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * 特定のサイトIDに紐づく総作品数を取得する
     */
    public function getTotalWorksCount(string $siteId): int
    {
        $totalSql = "SELECT COUNT(*) FROM works WHERE site_id = :siteId";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($totalSql);
        $stmt->bindValue(':siteId', $siteId, \PDO::PARAM_STR);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * 全サイトの最新作品を取得する
     */
    public function getLatestWorksAllSites(int $limit, int $offset = 0): array
    {
        $sql = "
            SELECT 
                w.*, 
                wa.link_title, 
                wa.capture_1_link
            FROM 
                works w
            LEFT JOIN 
                work_affiliates wa ON w.work_id = wa.work_id AND wa.site_id = w.site_id
            ORDER BY 
                w.release_date DESC, w.id DESC
            LIMIT :limit OFFSET :offset
        ";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $works = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // PHP側でアクター情報を結合するためのロジック
        $workIds = array_column($works, 'work_id');
        $actorMap = $this->getActorsByWorkIds($workIds);

        foreach ($works as &$work) {
            $work['actor_names'] = $actorMap[$work['work_id']] ?? '';
        }

        return $works;
    }

    /**
     * 全サイトの総作品数を取得する
     */
    public function getTotalWorksCountAllSites(): int
    {
        $totalSql = "SELECT COUNT(*) FROM works";
        $pdo = Database::getConnection();
        // WHERE句なし
        return (int) $pdo->query($totalSql)->fetchColumn();
    }

    /**
     * 単一の作品IDに基づき、全ての詳細データ（works & work_affiliates）を取得する
     * @param string $siteId サイトID (例: 'lemon')
     * @param string $workId 作品ID (例: '32665')
     */
    public function getWorkDetails(string $siteId, string $workId): ?array
    {
        // work_affiliates のデータもまとめて取得
        $sql = "SELECT w.*, wa.link_title, wa.capture_1_link, wa.capture_2_link, wa.capture_3_link
            FROM works w
            JOIN work_affiliates wa ON w.work_id = wa.work_id AND wa.site_id = w.site_id
            WHERE w.work_id = :workId AND w.site_id = :siteId /* ★ siteIdを検索条件に追加 */
            LIMIT 1";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);

        // workId のバインド
        $stmt->bindValue(':workId', $workId, \PDO::PARAM_STR);

        // siteId のバインド
        $stmt->bindValue(':siteId', $siteId, \PDO::PARAM_STR);

        $stmt->execute();
        $work = $stmt->fetch(\PDO::FETCH_ASSOC);

        // TODO: ここで作品に関連する出演者、タグなどのデータを取得・結合するロジックを追加する必要があります。

        return $work ?: null;
    }

    // ====================================================================
    // ★★★ 属性別作品検索メソッド (タグ/シリーズ/アクター) ★★★
    // ====================================================================

    /**
     * 特定のサイトIDとタグ名に紐づく作品リストを取得する
     * @param string $siteId 現在のサイトID ('okashi', 'lemon', 'b10f')
     * @param string $tagName タグ名
     * @param int $perPage 1ページあたりの件数
     * @param int $offset オフセット
     * @return array 作品データの配列
     */
    public function getWorksByTag(
        string $siteId,
        string $tagName,
        int $perPage,
        int $offset
    ): array {
        try {
            $pdo = \App\Core\Database::getConnection(); // データベース接続の取得

            // 1. WHERE句とパラメータの準備
            $where = "t.name COLLATE utf8mb4_general_ci = :tag_name";
            $params = [
                ':tag_name' => $tagName,
                ':limit' => $perPage,
                ':offset' => $offset,
                ':site_id' => $siteId // 常にサイトIDを含める
            ];

            // ★ 修正済み: site_idによる絞り込みを常に適用 (サイトID 'all' の処理を排除)
            $where .= " AND w.site_id COLLATE utf8mb4_general_ci = :site_id";

            // 2. クエリの構築
            $sql = "
                SELECT 
                    w.*,
                    GROUP_CONCAT(DISTINCT a.name) AS actor_names,
                    b.name AS brand_name
                FROM 
                    works w
                INNER JOIN 
                    work_tags wt ON w.work_id COLLATE utf8mb4_general_ci = wt.work_id
                INNER JOIN 
                    tags t ON wt.tag_id = t.id
                LEFT JOIN
                    work_actors wa ON w.work_id COLLATE utf8mb4_general_ci = wa.work_id
                LEFT JOIN
                    actors a ON wa.actor_id = a.id
                LEFT JOIN
                    work_brands wb ON w.work_id COLLATE utf8mb4_general_ci = wb.work_id AND w.site_id COLLATE utf8mb4_general_ci = wb.site_id
                LEFT JOIN
                    brands b ON wb.brand_id = b.id
                WHERE 
                    {$where}
                GROUP BY 
                    w.work_id, w.site_id
                ORDER BY 
                    w.release_date DESC, w.id DESC
                LIMIT :limit 
                OFFSET :offset;
            ";

            // 3. プリペアドステートメントの準備とバインド
            $stmt = $pdo->prepare($sql);

            foreach ($params as $key => &$val) {
                if ($key === ':limit' || $key === ':offset') {
                    // LIMIT/OFFSETは整数としてバインド
                    $stmt->bindValue($key, $val, \PDO::PARAM_INT);
                } else {
                    // その他は文字列としてバインド
                    $stmt->bindValue($key, $val, \PDO::PARAM_STR);
                }
            }

            // クエリの実行
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("WorksModel DB Error (getWorksByTag): " . $e->getMessage());
            return [];
        }
    }

    /**
     * 特定のタグに紐づく総作品数を取得する
     * @param string $siteId 現在のサイトID ('okashi', 'lemon', 'b10f')
     * @param string $tagName タグ名
     * @return int 総作品数
     */
    public function getTotalWorksCountByTag(string $siteId, string $tagName): int
    {
        try {
            $pdo = \App\Core\Database::getConnection(); // データベース接続の取得

            // 1. WHERE句とパラメータの準備
            $where = "t.name COLLATE utf8mb4_general_ci = :tag_name";
            $params = [
                ':tag_name' => $tagName,
                ':site_id' => $siteId // 常にサイトIDを含める
            ];

            // ★ 修正済み: site_idによる絞り込みを常に適用
            $where .= " AND w.site_id COLLATE utf8mb4_general_ci = :site_id";

            // 2. クエリの構築
            $sql = "
                SELECT 
                    COUNT(DISTINCT w.work_id) 
                FROM 
                    works w
                JOIN 
                    work_tags wt ON w.work_id COLLATE utf8mb4_general_ci = wt.work_id
                JOIN
                    tags t ON wt.tag_id = t.id
                WHERE 
                    {$where}
            ";

            // 3. プリペアドステートメントの準備と実行
            $stmt = $pdo->prepare($sql);

            // パラメータのバインド
            foreach ($params as $key => &$val) {
                $stmt->bindValue($key, $val, \PDO::PARAM_STR);
            }

            // クエリの実行
            $stmt->execute();

            // 結果の取得と返却
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("WorksModel DB Error (getTotalWorksCountByTag): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * 特定のサイトIDとブランド名に紐づく総作品数を取得する
     * @param string $siteId 現在のサイトID ('okashi', 'lemon', 'b10f')
     * @param string $brandName ブランド名
     * @return int 総作品数
     */
    public function getTotalWorksCountByBrand(string $siteId, string $brandName): int
    {
        try {
            $pdo = \App\Core\Database::getConnection(); // データベース接続の取得

            // 1. WHERE句とパラメータの準備
            $where = "b.name COLLATE utf8mb4_general_ci = :brand_name";
            $params = [
                ':brand_name' => $brandName,
                ':site_id' => $siteId // 常にサイトIDを含める
            ];

            // ★ 修正済み: site_idによる絞り込みを常に適用 (サイトID 'all' の処理を排除)
            $where .= " AND w.site_id COLLATE utf8mb4_general_ci = :site_id";

            // 2. クエリの構築
            $sql = "
                SELECT 
                    COUNT(DISTINCT w.work_id) 
                FROM 
                    works w
                INNER JOIN
                    work_brands wb ON w.work_id COLLATE utf8mb4_general_ci = wb.work_id 
                                   AND w.site_id COLLATE utf8mb4_general_ci = wb.site_id
                INNER JOIN
                    brands b ON wb.brand_id = b.id
                WHERE 
                    {$where}
            ";

            // 3. プリペアドステートメントの準備と実行
            $stmt = $pdo->prepare($sql);

            // パラメータのバインド
            foreach ($params as $key => &$val) {
                $stmt->bindValue($key, $val, \PDO::PARAM_STR);
            }

            // クエリの実行
            $stmt->execute();

            // 結果の取得と返却
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("WorksModel DB Error (getTotalWorksCountByBrand): " . $e->getMessage());
            return 0;
        }
    }


    /**
     * 特定のサイトIDとシリーズ名に紐づく作品リストを取得する
     * @param string $siteId 現在のサイトID ('okashi', 'lemon', 'b10f')
     * @param string $seriesName シリーズ名
     * @param int $perPage 1ページあたりの件数
     * @param int $offset オフセット
     * @return array 作品データの配列
     */
    public function getWorksBySeries(
        string $siteId,
        string $seriesName,
        int $perPage,
        int $offset
    ): array {
        try {
            $pdo = \App\Core\Database::getConnection(); // データベース接続の取得

            // 1. WHERE句とパラメータの準備
            $where = "s.name COLLATE utf8mb4_general_ci = :series_name";
            $params = [
                ':series_name' => $seriesName,
                ':limit' => $perPage,
                ':offset' => $offset,
                ':site_id' => $siteId // 常にサイトIDを含める
            ];

            // ★ 修正済み: site_idによる絞り込みを常に適用
            $where .= " AND w.site_id COLLATE utf8mb4_general_ci = :site_id";

            // 2. クエリの構築
            $sql = "
                SELECT 
                    w.*,
                    GROUP_CONCAT(DISTINCT a.name) AS actor_names,
                    b.name AS brand_name
                FROM 
                    works w
                INNER JOIN 
                    work_series ws ON w.work_id COLLATE utf8mb4_general_ci = ws.work_id
                INNER JOIN 
                    series s ON ws.series_id = s.id
                LEFT JOIN
                    work_actors wa ON w.work_id COLLATE utf8mb4_general_ci = wa.work_id
                LEFT JOIN
                    actors a ON wa.actor_id = a.id
                LEFT JOIN
                    work_brands wb ON w.work_id COLLATE utf8mb4_general_ci = wb.work_id AND w.site_id COLLATE utf8mb4_general_ci = wb.site_id
                LEFT JOIN
                    brands b ON wb.brand_id = b.id
                WHERE 
                    {$where}
                GROUP BY 
                    w.work_id, w.site_id
                ORDER BY 
                    w.release_date DESC, w.id DESC
                LIMIT :limit 
                OFFSET :offset;
            ";

            // 3. プリペアドステートメントの準備とバインド
            $stmt = $pdo->prepare($sql);

            foreach ($params as $key => &$val) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindValue($key, $val, \PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $val, \PDO::PARAM_STR);
                }
            }

            // クエリの実行
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("WorksModel DB Error (getWorksBySeries): " . $e->getMessage());
            return [];
        }
    }

    /**
     * 特定のシリーズに紐づく総作品数を取得する
     * @param string $siteId 現在のサイトID ('okashi', 'lemon', 'b10f')
     * @param string $seriesName シリーズ名
     * @return int 総作品数
     */
    public function getTotalWorksCountBySeries(string $siteId, string $seriesName): int
    {
        try {
            $pdo = \App\Core\Database::getConnection(); // データベース接続の取得

            // 1. WHERE句とパラメータの準備
            $where = "s.name COLLATE utf8mb4_general_ci = :series_name";
            $params = [
                ':series_name' => $seriesName,
                ':site_id' => $siteId // 常にサイトIDを含める
            ];

            // ★ 修正済み: site_idによる絞り込みを常に適用
            $where .= " AND w.site_id COLLATE utf8mb4_general_ci = :site_id";

            // 2. クエリの構築
            $sql = "
                SELECT 
                    COUNT(DISTINCT w.work_id) 
                FROM 
                    works w
                JOIN 
                    work_series ws ON w.work_id COLLATE utf8mb4_general_ci = ws.work_id
                JOIN
                    series s ON ws.series_id = s.id
                WHERE 
                    {$where}
            ";

            // 3. プリペアドステートメントの準備と実行
            $stmt = $pdo->prepare($sql);

            // パラメータのバインド
            foreach ($params as $key => &$val) {
                $stmt->bindValue($key, $val, \PDO::PARAM_STR);
            }

            // クエリの実行
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("WorksModel DB Error (getTotalWorksCountBySeries): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * 特定のサイトIDと出演者名に紐づく作品リストを取得する
     * @param string $siteId 現在のサイトID ('okashi', 'lemon', 'b10f')
     * @param string $actorName 出演者名
     * @param int $perPage 1ページあたりの件数
     * @param int $offset オフセット
     * @return array 作品データの配列
     */
    public function getWorksByActor(
        string $siteId,
        string $actorName,
        int $perPage,
        int $offset
    ): array {
        try {
            $pdo = \App\Core\Database::getConnection(); // データベース接続の取得

            // 1. WHERE句とパラメータの準備
            $where = "a.name COLLATE utf8mb4_general_ci = :actor_name";
            $params = [
                ':actor_name' => $actorName,
                ':limit' => $perPage,
                ':offset' => $offset,
                ':site_id' => $siteId // 常にサイトIDを含める
            ];

            // ★ 修正済み: site_idによる絞り込みを常に適用
            $where .= " AND w.site_id COLLATE utf8mb4_general_ci = :site_id";

            // 2. クエリの構築
            // NOTE: GROUP_CONCAT(a.name) を使うと、検索対象の俳優以外の名前も含まれるため、a.nameのエイリアスは使わない
            $sql = "
                SELECT 
                    w.*,
                    (SELECT GROUP_CONCAT(DISTINCT a2.name) FROM work_actors wa2 JOIN actors a2 ON wa2.actor_id = a2.id WHERE wa2.work_id = w.work_id) AS actor_names,
                    b.name AS brand_name
                FROM 
                    works w
                INNER JOIN 
                    work_actors wa ON w.work_id COLLATE utf8mb4_general_ci = wa.work_id
                INNER JOIN 
                    actors a ON wa.actor_id = a.id
                LEFT JOIN
                    work_brands wb ON w.work_id COLLATE utf8mb4_general_ci = wb.work_id AND w.site_id COLLATE utf8mb4_general_ci = wb.site_id
                LEFT JOIN
                    brands b ON wb.brand_id = b.id
                WHERE 
                    {$where}
                GROUP BY 
                    w.work_id, w.site_id
                ORDER BY 
                    w.release_date DESC, w.id DESC
                LIMIT :limit 
                OFFSET :offset;
            ";

            // 3. プリペアドステートメントの準備とバインド
            $stmt = $pdo->prepare($sql);

            foreach ($params as $key => &$val) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindValue($key, $val, \PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $val, \PDO::PARAM_STR);
                }
            }

            // クエリの実行
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("WorksModel DB Error (getWorksByActor): " . $e->getMessage());
            return [];
        }
    }

    /**
     * 特定の出演者に紐づく総作品数を取得する
     * @param string $siteId 現在のサイトID ('okashi', 'lemon', 'b10f')
     * @param string $actorName 出演者名
     * @return int 総作品数
     */
    public function getTotalWorksCountByActor(string $siteId, string $actorName): int
    {
        try {
            $pdo = \App\Core\Database::getConnection(); // データベース接続の取得

            // 1. WHERE句とパラメータの準備
            $where = "a.name COLLATE utf8mb4_general_ci = :actor_name";
            $params = [
                ':actor_name' => $actorName,
                ':site_id' => $siteId // 常にサイトIDを含める
            ];

            // ★ 修正済み: site_idによる絞り込みを常に適用
            $where .= " AND w.site_id COLLATE utf8mb4_general_ci = :site_id";

            // 2. クエリの構築
            $sql = "
                SELECT 
                    COUNT(DISTINCT w.work_id) 
                FROM 
                    works w
                JOIN 
                    work_actors wa ON w.work_id COLLATE utf8mb4_general_ci = wa.work_id
                JOIN
                    actors a ON wa.actor_id = a.id
                WHERE 
                    {$where}
            ";

            // 3. プリペアドステートメントの準備と実行
            $stmt = $pdo->prepare($sql);

            // パラメータのバインド
            foreach ($params as $key => &$val) {
                $stmt->bindValue($key, $val, \PDO::PARAM_STR);
            }

            // クエリの実行
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("WorksModel DB Error (getTotalWorksCountByActor): " . $e->getMessage());
            return 0;
        }
    }

    // ====================================================================
    // ★★★ 作品データ永続化 (UPSERT) メソッド ★★★
    // ====================================================================

    /**
     * worksテーブルに作品データを挿入または更新する（UPSERT）
     */
    public function insertOrUpdateWork(array $data): bool
    {
        $pdo = Database::getConnection();
        // 挿入するカラムと値
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);

        // ON DUPLICATE KEY UPDATE 句を生成 (主キー以外を更新対象とする)
        $updateFields = [];
        foreach ($columns as $col) {
            if ($col !== 'work_id' && $col !== 'site_id') {
                $updateFields[] = "$col = VALUES($col)";
            }
        }
        $updateClause = implode(', ', $updateFields);

        $sql = "
            INSERT INTO works (" . implode(', ', $columns) . ") 
            VALUES (" . implode(', ', $placeholders) . ") 
            ON DUPLICATE KEY UPDATE 
                $updateClause
        ";

        try {
            $stmt = $pdo->prepare($sql);
            // データをバインド
            foreach ($data as $key => &$value) {
                // bindParamは参照渡しが必要なため、&$valueを使用
                $stmt->bindParam(":$key", $value);
            }
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("WorksModel insertOrUpdateWork DB Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * work_affiliates テーブルにアフィリエイトリンクデータを挿入または更新する (UPSERT)
     */
    public function insertOrUpdateWorkAffiliates(int $workId, string $siteId, array $affiliateData): bool
    {
        $pdo = Database::getConnection();

        // 必須データ (work_id, site_id) とタイムスタンプを追加
        $data = array_merge([
            'work_id' => $workId,
            'site_id' => $siteId,
            'updated_at' => date('Y-m-d H:i:s')
        ], $affiliateData);

        // 挿入するカラムとプレースホルダ
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);

        // ON DUPLICATE KEY UPDATE 句を生成 (work_id, site_id, id 以外を更新対象とする)
        $updateFields = [];
        foreach ($columns as $col) {
            if ($col !== 'work_id' && $col !== 'site_id' && $col !== 'id') {
                $updateFields[] = "$col = VALUES($col)";
            }
        }

        // 更新対象がない場合でも、updated_atは更新対象とする
        $updateClause = implode(', ', $updateFields);
        if (empty($updateClause)) {
            $updateClause = "updated_at = VALUES(updated_at)";
        }

        $sql = "
            INSERT INTO work_affiliates (" . implode(', ', $columns) . ") 
            VALUES (" . implode(', ', $placeholders) . ") 
            ON DUPLICATE KEY UPDATE 
                $updateClause
        ";

        try {
            $stmt = $pdo->prepare($sql);
            foreach ($data as $key => &$value) {
                // work_id は INT、その他は STR としてバインド
                $paramType = ($key === 'work_id') ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $stmt->bindValue(":$key", $value, $paramType);
            }
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("WorksModel insertOrUpdateWorkAffiliates DB Error: " . $e->getMessage());
            return false;
        }
    }


    // ====================================================================
    // ★★★ 関連エンティティ (マスターデータ & 関連付け) メソッド ★★★
    // ====================================================================

    // --- アクター/女優関連 ---

    /**
     * 複数の作品IDに基づき、アクター名リストを取得する
     */
    public function getActorsByWorkIds(array $workIds): array
    {
        if (empty($workIds)) {
            return [];
        }

        // プレースホルダを生成 (例: ?, ?, ?)
        $placeholders = implode(', ', array_fill(0, count($workIds), '?'));
        $sql = "
            SELECT 
                w_a.work_id,
                GROUP_CONCAT(a.name ORDER BY a.id SEPARATOR ', ') AS actor_names
            FROM 
                work_actors w_a
            JOIN
                actors a ON w_a.actor_id = a.id
            WHERE
                w_a.work_id IN ({$placeholders})
            GROUP BY 
                w_a.work_id
        ";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);

        // work_idの配列を直接バインド
        $stmt->execute($workIds);

        // work_idをキー、actor_namesを値とする連想配列に変換
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $actorMap = [];
        foreach ($results as $row) {
            $actorMap[$row['work_id']] = $row['actor_names'];
        }

        return $actorMap;
    }

    /**
     * 女優を挿入またはIDを取得し、actor_sitesテーブルに関連付けを登録する
     */
    public function insertOrGetActor(string $actorNameRaw, string $siteId): int
    {
        // 女優名から前後の空白を削除
        $actorName = trim($actorNameRaw);
        if (empty($actorName)) {
            return 0;
        }

        $pdo = Database::getConnection();
        // 1. actorsテーブル: 存在チェック
        $sql = "SELECT id FROM actors WHERE name = :name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':name' => $actorName]);
        $actorId = $stmt->fetchColumn();

        // 2. actorsテーブル: 挿入
        if (!$actorId) {
            $sql = "INSERT INTO actors (name) VALUES (:name)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':name' => $actorName]);
            $actorId = $pdo->lastInsertId();
        }

        $actorId = (int)$actorId;
        if ($actorId > 0) {
            // 3. actor_sitesテーブルへの関連付け (UPSERT)
            $sqlActorSite = "
                INSERT INTO actor_sites (actor_id, site_id) 
                VALUES (:actor_id, :site_id)
                ON DUPLICATE KEY UPDATE 
                actor_id = actor_id
            ";
            $stmtActorSite = $pdo->prepare($sqlActorSite);
            $stmtActorSite->bindValue(':actor_id', $actorId, \PDO::PARAM_INT);
            $stmtActorSite->bindValue(':site_id', $siteId, \PDO::PARAM_STR);
            $stmtActorSite->execute();
        }

        return $actorId;
    }

    /**
     * work_actorsテーブルに作品と女優の関連付けを挿入/更新する
     */
    public function insertWorkActorLink($workId, $siteId, $actorId) // 型ヒントと戻り値の型宣言は削除された状態で再構築
    {
        $pdo = Database::getConnection();
        // 既存の関連付けを削除（上書き対応）
        $deleteSql = "DELETE FROM work_actors WHERE work_id = :work_id AND site_id = :site_id";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->execute([':work_id' => $workId, ':site_id' => $siteId]);

        // 新しい関連付けを挿入 (INSERT IGNORE)
        $sql = "INSERT IGNORE INTO work_actors 
            (work_id, site_id, actor_id) 
            VALUES 
            (:work_id, :site_id, :actor_id)";
        try {
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                ':work_id' => $workId,
                ':site_id' => $siteId,
                ':actor_id' => $actorId,
            ]);
        } catch (\PDOException $e) {
            error_log("WorksModel insertWorkActorLink DB Error: " . $e->getMessage());
            return false;
        }
    }


    // --- タグ関連 ---

    /**
     * タグを挿入またはIDを取得し、tag_sitesテーブルに関連付けを登録する
     */
    public function insertOrGetTag(string $tagNameRaw, string $siteId): int
    {
        $tagName = trim($tagNameRaw);
        if (empty($tagName)) {
            return 0;
        }

        $pdo = Database::getConnection();
        // 1. tagsテーブル: 存在チェック
        $sql = "SELECT id FROM tags WHERE name = :name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':name' => $tagName]);
        $tagId = $stmt->fetchColumn();

        // 2. tagsテーブル: 挿入
        if (!$tagId) {
            $sql = "INSERT INTO tags (name) VALUES (:name)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':name' => $tagName]);
            $tagId = $pdo->lastInsertId();
        }

        $tagId = (int)$tagId;
        if ($tagId > 0) {
            // 3. tag_sitesテーブルへの関連付け (UPSERT)
            $sqlTagSite = "
                INSERT INTO tag_sites (tag_id, site_id) 
                VALUES (:tag_id, :site_id)
                ON DUPLICATE KEY UPDATE 
                tag_id = tag_id /* 主キーを自分自身で更新し、エラーを回避 */
            ";
            $stmtTagSite = $pdo->prepare($sqlTagSite);
            $stmtTagSite->bindValue(':tag_id', $tagId, \PDO::PARAM_INT);
            $stmtTagSite->bindValue(':site_id', $siteId, \PDO::PARAM_STR);
            $stmtTagSite->execute();
        }

        return $tagId;
    }

    /**
     * work_tagsテーブルに作品とタグの関連付けを挿入/更新する
     */
    public function insertWorkTags(string $workId, string $siteId, array $tagNames): bool
    {
        $pdo = Database::getConnection();
        // 1. 既存の関連付けを削除（上書き）
        $deleteSql = "DELETE FROM work_tags WHERE work_id = :work_id AND site_id = :site_id";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->execute([':work_id' => $workId, ':site_id' => $siteId]);

        if (empty($tagNames)) {
            return true;
        }

        // 2. 新しい関連付けを挿入
        $sql = "INSERT IGNORE INTO work_tags (work_id, site_id, tag_id) VALUES (:work_id, :site_id, :tag_id)";
        try {
            $stmt = $pdo->prepare($sql);
            foreach ($tagNames as $tagName) {
                // タグマスターに登録しIDを取得
                $tagId = $this->insertOrGetTag($tagName, $siteId);

                // 関連付けを挿入
                if ($tagId > 0) {
                    $stmt->execute([
                        ':work_id' => $workId,
                        ':site_id' => $siteId,
                        ':tag_id' => $tagId,
                    ]);
                }
            }
            return true;
        } catch (\PDOException $e) {
            error_log("WorksModel insertWorkTags DB Error: " . $e->getMessage());
            return false;
        }
    }


    /**
     * ブランドを挿入またはIDを取得し、brands_sitesテーブルに関連付けを登録する
     * @param string $brandNameRaw 処理前のブランド名
     * @param string $siteId 関連付けるサイトID
     * @return int 挿入または取得したブランドID (失敗時は0)
     */
    public function insertOrGetBrand(string $brandNameRaw, string $siteId): int
    {
        $brandName = trim($brandNameRaw);
        if (empty($brandName)) {
            return 0;
        }

        $pdo = \App\Core\Database::getConnection();

        try {
            // ★★★ 修正箇所：トランザクション開始 ($pdo->beginTransaction();) を削除 ★★★

            // 1. brandsテーブル: 存在チェック
            // カラム名 'id' は以前の修正で確認済み
            $sql = "SELECT id FROM brands WHERE name = :name";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':name' => $brandName]);
            $brandId = $stmt->fetchColumn();

            // 2. brandsテーブル: 挿入
            if (!$brandId) {
                // 必須カラムを全て含めたSQL
                $sql = "
                INSERT INTO brands (name, work_count, created_at, updated_at) 
                VALUES (:name, 0, NOW(), NOW())
            ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':name' => $brandName]);
                $brandId = $pdo->lastInsertId();
            }

            $brandId = (int)$brandId;

            // 3. brands_sitesテーブルへの関連付け (UPSERT)
            // カラム名 'brand_id' は以前の ALTER TABLE で修正済み
            if ($brandId > 0) {
                $sqlBrandSite = " 
                INSERT INTO brands_sites (brand_id, site_id) 
                VALUES (:brand_id, :site_id)
                ON DUPLICATE KEY UPDATE 
                brand_id = VALUES(brand_id)
            ";
                $stmtBrandSite = $pdo->prepare($sqlBrandSite);
                $stmtBrandSite->bindValue(':brand_id', $brandId, \PDO::PARAM_INT);
                $stmtBrandSite->bindValue(':site_id', $siteId, \PDO::PARAM_STR);
                $stmtBrandSite->execute();
            }

            // ★★★ 修正箇所：コミット ($pdo->commit();) を削除 ★★★
            return $brandId;
        } catch (\PDOException $e) {
            // ★★★ 修正箇所：ロールバック処理を削除 ★★★
            error_log("WorksModel insertOrGetBrand DB Error: " . $e->getMessage());
            return 0;
        }
    }
    /**
     * work_brandsテーブルに作品とブランドの関連付けを挿入/更新する
     * ※ 以前の同名メソッドの重複を解消し、引数名をbrandIdに統一
     * * @param string $workId 作品ID
     * @param string $siteId サイトID
     * @param int $brandId ブランドID
     * @param string $linkHtml リンクHTML
     * @return bool 実行成功/失敗
     */
    public function insertWorkBrandLink(string $workId, string $siteId, int $brandId, string $linkHtml): bool
    {
        $pdo = \App\Core\Database::getConnection();
        $sql = " 
            INSERT INTO work_brands (work_id, site_id, brand_id, link_html) 
            VALUES (:work_id, :site_id, :brand_id, :link_html) 
            ON DUPLICATE KEY UPDATE 
            brand_id = VALUES(brand_id),
            link_html = VALUES(link_html)
        ";
        try {
            $stmt = $pdo->prepare($sql);

            // パラメータのバインド (省略なし)
            $stmt->bindValue(':work_id', $workId, \PDO::PARAM_STR);
            $stmt->bindValue(':site_id', $siteId, \PDO::PARAM_STR);
            $stmt->bindValue(':brand_id', $brandId, \PDO::PARAM_INT);
            $stmt->bindValue(':link_html', $linkHtml, \PDO::PARAM_STR);

            // クエリの実行
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("WorksModel insertWorkBrandLink DB Error: " . $e->getMessage());
            return false;
        }
    }


    /**
     * レーベルを挿入またはIDを取得する
     */
    public function insertOrGetLabel(string $labelName): int
    {
        $db = Database::getConnection();
        // 1. 既存のレーベル名で検索 
        $sql = "SELECT id FROM labels WHERE name = ?"; // idカラムを仮定
        $stmt = $db->prepare($sql);
        $stmt->execute([$labelName]);
        $labelId = $stmt->fetchColumn();

        if ($labelId) {
            return (int)$labelId;
        }

        // 2. 挿入
        $sql = "INSERT INTO labels (name, work_count) VALUES (?, 0)"; // work_countを仮定
        $stmt = $db->prepare($sql);
        $stmt->execute([$labelName]);
        return (int)$db->lastInsertId();
    }

    /**
     * work_labelsテーブルに作品とレーベルの関連付けを挿入/更新する
     */
    public function insertWorkLabelLink(string $workId, string $siteId, int $labelId, string $linkHtml): bool
    {
        $pdo = Database::getConnection();
        $sql = "
            INSERT INTO work_labels 
                (work_id, site_id, label_id, link_html) 
            VALUES 
                (:work_id, :site_id, :label_id, :link_html)
            ON DUPLICATE KEY UPDATE 
                label_id = VALUES(label_id), 
                link_html = VALUES(link_html)
        ";
        try {
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                ':work_id' => $workId,
                ':site_id' => $siteId,
                ':label_id' => $labelId,
                ':link_html' => $linkHtml,
            ]);
        } catch (\PDOException $e) {
            error_log("WorksModel insertWorkLabelLink DB Error: " . $e->getMessage());
            return false;
        }
    }

    // --- シリーズ関連 ---

    /**
     * シリーズを挿入またはIDを取得する
     */
    public function insertOrGetSeries(string $seriesName, string $siteId): int
    {
        $seriesName = trim($seriesName);
        if (empty($seriesName)) {
            return 0;
        }

        $pdo = Database::getConnection();
        // 1. seriesテーブル: 存在チェック
        $sql = "SELECT id FROM series WHERE name = :name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':name' => $seriesName]);
        $seriesId = $stmt->fetchColumn();

        // 2. seriesテーブル: 挿入
        if (!$seriesId) {
            // work_count も 0 で挿入
            $sql = "INSERT INTO series (name, work_count) VALUES (:name, 0)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':name' => $seriesName]);
            $seriesId = $pdo->lastInsertId();
        }

        $seriesId = (int)$seriesId;
        if ($seriesId > 0) {
            // 3. series_sitesテーブルへの関連付け (UPSERT)
            $sqlSeriesSite = "
                INSERT INTO series_sites (series_id, site_id) 
                VALUES (:series_id, :site_id)
                ON DUPLICATE KEY UPDATE 
                series_id = series_id
            ";
            $stmtSeriesSite = $pdo->prepare($sqlSeriesSite);
            $stmtSeriesSite->bindValue(':series_id', $seriesId, \PDO::PARAM_INT);
            $stmtSeriesSite->bindValue(':site_id', $siteId, \PDO::PARAM_STR);
            $stmtSeriesSite->execute();
        }

        return $seriesId;
    }

    /**
     * work_seriesテーブルに作品とシリーズの関連付けを挿入/更新する
     */
    public function insertWorkSeriesLink(string $workId, string $siteId, int $seriesId, string $linkHtml): bool
    {
        $pdo = Database::getConnection();
        $sql = "
            INSERT INTO work_series 
                (work_id, site_id, series_id, link_html) 
            VALUES 
                (:work_id, :site_id, :series_id, :link_html)
            ON DUPLICATE KEY UPDATE 
                series_id = VALUES(series_id), 
                link_html = VALUES(link_html)
        ";
        try {
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                ':work_id' => $workId,
                ':site_id' => $siteId,
                ':series_id' => $seriesId,
                ':link_html' => $linkHtml,
            ]);
        } catch (\PDOException $e) {
            error_log("WorksModel insertWorkSeriesLink DB Error: " . $e->getMessage());
            return false;
        }
    }

    // --- キャプチャ画像関連 ---

    /**
     * キャプチャ画像の情報をwork_capturesテーブルに挿入する
     */
    public function insertCaptures(string $workId, string $siteId, array $captures): bool
    {
        $pdo = Database::getConnection();
        // 既存のデータを削除 (インポート時の上書き対応)
        $deleteSql = "DELETE FROM work_captures WHERE work_id = :work_id AND site_id = :site_id";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->execute([':work_id' => $workId, ':site_id' => $siteId]);

        // 新しいデータを挿入
        $sql = "INSERT INTO work_captures 
            (work_id, site_id, capture_number, image_url, link_html) 
            VALUES 
            (:work_id, :site_id, :capture_number, :image_url, :link_html)";
        try {
            $stmt = $pdo->prepare($sql);
            foreach ($captures as $capture) {
                $stmt->execute([
                    ':work_id'         => $workId,
                    ':site_id'         => $siteId,
                    ':capture_number'  => $capture['capture_number'],
                    ':image_url'       => $capture['image_url'],
                    ':link_html'       => $capture['link_html'],
                ]);
            }
            return true;
        } catch (\PDOException $e) {
            error_log("WorksModel insertCaptures DB Error: " . $e->getMessage());
            return false;
        }
    }


    // ====================================================================
    // ★★★ カウント再計算メソッド (分離・修正) ★★★
    // ====================================================================

    /**
     * アクター、タグ、ブランド（メーカー）の work_count を再計算し更新する
     */
    public function recalculateCounts(): void
    {
        $pdo = Database::getConnection();

        // ----------------------------------------------------------------------
        // 1. ブランド (brands ) カウントの再計算
        // ----------------------------------------------------------------------
        $sqlBrand = "
            UPDATE brands b
            JOIN (
                SELECT brand_id, COUNT(work_id) as count
                FROM work_brands
                GROUP BY brand_id
            ) AS wb_counts
            ON b.id = wb_counts.brand_id
            SET b.work_count = wb_counts.count
        ";
        $pdo->exec($sqlBrand);

        // 作品がないブランドの work_count を 0 にリセット
        $sqlBrandReset = "
            UPDATE brands 
            SET work_count = 0
            WHERE id NOT IN (SELECT DISTINCT brand_id FROM work_brands);
        ";
        $pdo->exec($sqlBrandReset);


        // ----------------------------------------------------------------------
        // 2. タグ (tags) カウントの再計算
        // ----------------------------------------------------------------------
        $sqlTag = "
            UPDATE tags t
            JOIN (
                SELECT tag_id, COUNT(work_id) AS count
                FROM work_tags
                GROUP BY tag_id
            ) AS wt_counts
            ON t.id = wt_counts.tag_id
            SET t.work_count = wt_counts.count
        ";
        $pdo->exec($sqlTag);

        // 作品がないタグの work_count を 0 にリセット
        $sqlTagReset = "
            UPDATE tags 
            SET work_count = 0
            WHERE id NOT IN (SELECT DISTINCT tag_id FROM work_tags);
        ";
        $pdo->exec($sqlTagReset);


        // ----------------------------------------------------------------------
        // 3. 女優 (actors) カウントの再計算
        // ----------------------------------------------------------------------
        $sqlActor = "
            UPDATE actors a
            JOIN (
                SELECT actor_id, COUNT(work_id) AS count
                FROM work_actors
                GROUP BY actor_id
            ) AS wa_counts
            ON a.id = wa_counts.actor_id
            SET a.work_count = wa_counts.count
        ";
        $pdo->exec($sqlActor);

        // 作品がない女優の work_count を 0 にリセット
        $sqlActorReset = "
            UPDATE actors 
            SET work_count = 0
            WHERE id NOT IN (SELECT DISTINCT actor_id FROM work_actors);
        ";
        $pdo->exec($sqlActorReset);
    }

    /**
     * レーベル (labels) の work_count を再計算し更新する
     */
    public function recalculateLabelCounts(): void
    {
        $pdo = Database::getConnection();
        // ----------------------------------------------------------------------
        // レーベル (labels) カウントの再計算
        // ----------------------------------------------------------------------
        $sqlLabel = "
            UPDATE labels l
            JOIN (
                SELECT label_id, COUNT(work_id) as count
                FROM work_labels
                GROUP BY label_id
            ) AS wl_counts
            ON l.id = wl_counts.label_id
            SET l.work_count = wl_counts.count
        ";
        $pdo->exec($sqlLabel);

        // 作品がないレーベルの work_count を 0 にリセット
        $sqlLabelReset = "
            UPDATE labels 
            SET work_count = 0
            WHERE id NOT IN (SELECT DISTINCT label_id FROM work_labels);
        ";
        $pdo->exec($sqlLabelReset);
    }


    /**
     * ブランド (brands) の work_count を再計算し更新する
     */
    public function recalculateBrandCounts(): void
    {
        $pdo = Database::getConnection();
        // ----------------------------------------------------------------------
        // ブランド (brands) カウントの再計算
        // ----------------------------------------------------------------------
        $sqlBrand = "
            UPDATE brands b
            JOIN (
                SELECT brand_id, COUNT(work_id) as count
                FROM work_brands
                GROUP BY brand_id
            ) AS wb_counts
            ON b.id = wb_counts.brand_id
            SET b.work_count = wb_counts.count
        ";
        $pdo->exec($sqlBrand);

        // 作品がないブランドの work_count を 0 にリセット
        $sqlBrandReset = "
            UPDATE brands 
            SET work_count = 0
            WHERE id NOT IN (SELECT DISTINCT brand_id FROM work_brands);
        ";
        $pdo->exec($sqlBrandReset);
    }

    /**
     * シリーズ (series) の work_count を再計算し更新する
     */
    public function recalculateSeriesCounts(): void
    {
        $pdo = Database::getConnection();
        // ----------------------------------------------------------------------
        // シリーズ (series) カウントの再計算
        // ----------------------------------------------------------------------
        $sqlSeries = "
            UPDATE series s
            JOIN (
                SELECT series_id, COUNT(work_id) AS count
                FROM work_series
                GROUP BY series_id
            ) AS ws_counts
            ON s.id = ws_counts.series_id
            SET s.work_count = ws_counts.count
        ";
        $pdo->exec($sqlSeries);

        // 作品がないシリーズの work_count を 0 にリセット
        $sqlSeriesReset = "
            UPDATE series 
            SET work_count = 0
            WHERE id NOT IN (SELECT DISTINCT series_id FROM work_series);
        ";
        $pdo->exec($sqlSeriesReset);
    }

    /**
     * 主要なテーブルのレコード数を取得する
     * @return array テーブル名とカウントの連想配列
     */
    public function getTableCounts(): array
    {
        // WorksModel.php は App\Core\Database を use していることを前提とします
        $pdo = \App\Core\Database::getConnection();
        $counts = [];
        $tables = [
            'works',
            'actors',
            'tags',
            'brands',
            'series',
            'labels',
            'work_actors',
            'work_tags',
            'work_labels',
            'work_brands',
            'work_series',
            'work_captures'
        ];

        foreach ($tables as $table) {
            try {
                // バッククォートを使用してテーブル名を保護
                $stmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
                $counts[$table] = (int)$stmt->fetchColumn();
            } catch (\PDOException $e) {
                // テーブルが存在しない、またはアクセスエラーの場合
                $counts[$table] = "ERROR: " . $e->getMessage();
            }
        }
        return $counts;
    }

    /**
     * 特定のサイトIDとブランド名に紐づく作品リストを取得する
     * @param string $siteId 現在のサイトID ('okashi', 'lemon', 'b10f')
     * @param string $brandName ブランド名
     * @param int $perPage 1ページあたりの件数
     * @param int $offset オフセット
     * @return array 作品データの配列
     */
    public function getWorksByBrand(
        string $siteId,
        string $brandName,
        int $perPage,
        int $offset
    ): array {
        try {
            $pdo = \App\Core\Database::getConnection(); // データベース接続の取得

            // 1. WHERE句とパラメータの準備
            $where = "b.name COLLATE utf8mb4_general_ci = :brand_name";
            $params = [
                ':brand_name' => $brandName,
                ':limit' => $perPage,
                ':offset' => $offset,
                ':site_id' => $siteId // 常にサイトIDを含める
            ];

            // ★ 修正済み: site_idによる絞り込みを常に適用 (サイトID 'all' の処理を排除)
            $where .= " AND w.site_id COLLATE utf8mb4_general_ci = :site_id";

            // 2. クエリの構築
            $sql = "
                SELECT 
                    w.*,
                    GROUP_CONCAT(DISTINCT a.name) AS actor_names,
                    b.name AS brand_name
                FROM 
                    works w
                INNER JOIN
                    work_brands wb ON w.work_id COLLATE utf8mb4_general_ci = wb.work_id AND w.site_id COLLATE utf8mb4_general_ci = wb.site_id
                INNER JOIN
                    brands b ON wb.brand_id = b.id
                LEFT JOIN
                    work_actors wa ON w.work_id COLLATE utf8mb4_general_ci = wa.work_id
                LEFT JOIN
                    actors a ON wa.actor_id = a.id
                WHERE 
                    {$where}
                GROUP BY 
                    w.work_id, w.site_id
                ORDER BY 
                    w.release_date DESC, w.id DESC
                LIMIT :limit 
                OFFSET :offset;
            ";

            // 3. プリペアドステートメントの準備とバインド
            $stmt = $pdo->prepare($sql);

            foreach ($params as $key => &$val) {
                if ($key === ':limit' || $key === ':offset') {
                    // LIMIT/OFFSETは整数としてバインド
                    $stmt->bindValue($key, $val, \PDO::PARAM_INT);
                } else {
                    // その他は文字列としてバインド
                    $stmt->bindValue($key, $val, \PDO::PARAM_STR);
                }
            }

            // クエリの実行
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("WorksModel DB Error (getWorksByBrand): " . $e->getMessage());
            return [];
        }
    }

    /**
     * 特定のサイトIDとレーベル名に紐づく作品リストを取得する
     * @param string $siteId 現在のサイトID ('okashi', 'lemon', 'b10f')
     * @param string $labelName レーベル名
     * @param int $perPage 1ページあたりの件数
     * @param int $offset オフセット
     * @return array 作品データの配列
     */
    public function getWorksByLabel(
        string $siteId,
        string $labelName,
        int $perPage,
        int $offset
    ): array {
        try {
            $pdo = \App\Core\Database::getConnection(); // データベース接続の取得

            // 1. WHERE句とパラメータの準備
            $where = "l.name COLLATE utf8mb4_general_ci = :label_name";
            $params = [
                ':label_name' => $labelName,
                ':limit' => $perPage,
                ':offset' => $offset,
                ':site_id' => $siteId // 常にサイトIDを含める
            ];

            // ★ 修正済み: site_idによる絞り込みを常に適用
            $where .= " AND w.site_id COLLATE utf8mb4_general_ci = :site_id";

            // 2. クエリの構築
            $sql = "
                SELECT 
                    w.*,
                    GROUP_CONCAT(DISTINCT a.name) AS actor_names,
                    b.name AS brand_name
                FROM 
                    works w
                INNER JOIN 
                    work_labels wl ON w.work_id COLLATE utf8mb4_general_ci = wl.work_id
                INNER JOIN 
                    labels l ON wl.label_id = l.id
                LEFT JOIN
                    work_actors wa ON w.work_id COLLATE utf8mb4_general_ci = wa.work_id
                LEFT JOIN
                    actors a ON wa.actor_id = a.id
                LEFT JOIN
                    work_brands wb ON w.work_id COLLATE utf8mb4_general_ci = wb.work_id AND w.site_id COLLATE utf8mb4_general_ci = wb.site_id
                LEFT JOIN
                    brands b ON wb.brand_id = b.id
                WHERE 
                    {$where}
                GROUP BY 
                    w.work_id, w.site_id
                ORDER BY 
                    w.release_date DESC, w.id DESC
                LIMIT :limit 
                OFFSET :offset;
            ";

            // 3. プリペアドステートメントの準備とバインド
            $stmt = $pdo->prepare($sql);

            foreach ($params as $key => &$val) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindValue($key, $val, \PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $val, \PDO::PARAM_STR);
                }
            }

            // クエリの実行
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("WorksModel DB Error (getWorksByLabel): " . $e->getMessage());
            return [];
        }
    }

    /**
     * 特定のレーベルに紐づく総作品数を取得する
     * @param string $siteId 現在のサイトID ('okashi', 'lemon', 'b10f')
     * @param string $labelName レーベル名
     * @return int 総作品数
     */
    public function getTotalWorksCountByLabel(string $siteId, string $labelName): int
    {
        try {
            $pdo = \App\Core\Database::getConnection(); // データベース接続の取得

            // 1. WHERE句とパラメータの準備
            $where = "l.name COLLATE utf8mb4_general_ci = :label_name";
            $params = [
                ':label_name' => $labelName,
                ':site_id' => $siteId // 常にサイトIDを含める
            ];

            // ★ 修正済み: site_idによる絞り込みを常に適用
            $where .= " AND w.site_id COLLATE utf8mb4_general_ci = :site_id";

            // 2. クエリの構築
            $sql = "
                SELECT 
                    COUNT(DISTINCT w.work_id) 
                FROM 
                    works w
                JOIN 
                    work_labels wl ON w.work_id COLLATE utf8mb4_general_ci = wl.work_id
                JOIN
                    labels l ON wl.label_id = l.id
                WHERE 
                    {$where}
            ";

            // 3. プリペアドステートメントの準備と実行
            $stmt = $pdo->prepare($sql);

            // パラメータのバインド
            foreach ($params as $key => &$val) {
                $stmt->bindValue($key, $val, \PDO::PARAM_STR);
            }

            // クエリの実行
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("WorksModel DB Error (getTotalWorksCountByLabel): " . $e->getMessage());
            return 0;
        }
    }



    /**
     * 最新の作品リストを取得する
     * @param string $siteId サイトID (例: 'okashi' または特定のサイトID)
     * @param int $limit 取得件数
     * @return array 作品データの配列
     */
    // ★ デフォルト値を 'okashi' に変更
    public function getLatestWorkList(string $siteId = 'okashi', int $limit = 20): array
    {
        // work_affiliates, actor_names, tag_names を含む結合クエリ
        $sql = "SELECT w.*, wa.link_title
            FROM works w
            JOIN work_affiliates wa ON w.work_id = wa.work_id AND wa.site_id = w.site_id";

        $conditions = [];
        $params = [];

        // ★ 'okashi' であっても、フィルタリング条件を適用
        // siteId が渡された値（デフォルトの 'okashi' または URLから渡されたサイトID）と一致する作品をフィルタ
        $conditions[] = "w.site_id = :siteId";
        $params[':siteId'] = $siteId;


        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        // 最新順に並び替え、リミットを適用
        $sql .= " ORDER BY w.release_date DESC, w.id DESC LIMIT :limit";

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);

        // パラメータをバインド
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);

        $stmt->execute();
        $works = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // TODO: ここで作品ごとに actor_list, tag_list, series_name を取得し、結合する処理が必要です。

        return $works;
    }

    /**
     * 基本作品データに、女優、タグ、シリーズの情報を結合して返す。
     * 既存のヘルパーメソッド（getActorNamesなど）を流用する。
     * * @param array $work 基本作品データ（work_id, site_idを含む）
     * @return array 関連情報が付与された作品データ
     */
    public function getWorkDataWithRelations(array $work): array
    {
        // 既存のヘルパーメソッドを呼び出して関連データを取得し、結合する
        // Next.jsが期待する actor_list, tag_list, series_name を付与
        
        $work['actor_list'] = $this->getActorNames($work['work_id'], $work['site_id']);
        $work['tag_list'] = $this->getTagNames($work['work_id'], $work['site_id']);
        
        // series_name は null 許容のため、取得結果をそのまま格納
        $work['series_name'] = $this->getSeriesName($work['work_id'], $work['site_id']);
        
        // Next.jsの型に合わせるため、シリーズ名が空文字の場合は null に変換
        if (empty($work['series_name'])) {
            $work['series_name'] = null;
        }

        return $work;
    }

    // ====================================================================
// ★★★ 作品一覧API用ヘルパーメソッド ★★★
// ====================================================================

    /**
     * 特定の作品IDに紐づく女優名リストを取得する
     * @param string $workId 作品ID
     * @param string $siteId サイトID
     * @return array 女優名の配列 (例: ['女優A', '女優B'])
     */
    public function getActorNames(string $workId, string $siteId): array
    {
        $sql = "
            SELECT 
                a.name
            FROM 
                work_actors wa
            JOIN
                actors a ON wa.actor_id = a.id
            WHERE
                wa.work_id = :workId AND wa.site_id = :siteId
        ";
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':workId', $workId, \PDO::PARAM_STR);
        $stmt->bindValue(':siteId', $siteId, \PDO::PARAM_STR);
        $stmt->execute();
        
        // PDO::FETCH_COLUMN を使うことで、結果を1次元の配列として取得できる
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * 特定の作品IDに紐づくタグ名リストを取得する
     * @param string $workId 作品ID
     * @param string $siteId サイトID
     * @return array タグ名の配列 (例: ['タグX', 'タグY'])
     */
    public function getTagNames(string $workId, string $siteId): array
    {
        $sql = "
            SELECT 
                t.name
            FROM 
                work_tags wt
            JOIN
                tags t ON wt.tag_id = t.id
            WHERE
                wt.work_id = :workId AND wt.site_id = :siteId
        ";
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':workId', $workId, \PDO::PARAM_STR);
        $stmt->bindValue(':siteId', $siteId, \PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * 特定の作品IDに紐づくシリーズ名を取得する
     * @param string $workId 作品ID
     * @param string $siteId サイトID
     * @return string|null シリーズ名、またはnull
     */
    public function getSeriesName(string $workId, string $siteId): ?string
    {
        $sql = "
            SELECT 
                s.name
            FROM 
                work_series ws
            JOIN
                series s ON ws.series_id = s.id
            WHERE
                ws.work_id = :workId AND ws.site_id = :siteId
            LIMIT 1
        ";
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':workId', $workId, \PDO::PARAM_STR);
        $stmt->bindValue(':siteId', $siteId, \PDO::PARAM_STR);
        $stmt->execute();
        
        $name = $stmt->fetchColumn();
        return $name !== false ? (string)$name : null;
    }


}

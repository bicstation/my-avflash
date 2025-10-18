<?php
// /home/wp552476/avflash.xyz/public_html/app/models/TagModel.php

namespace App\Models;

use App\Core\Database; // ご提示いただいたDatabaseクラスを利用
use PDO;
use PDOException;

class TagModel
{
    /**
     * 特定のサイトIDとタグ名に一致するタグの情報をデータベースから取得する
     *
     * @param string $siteId 対象のサイトID (例: 'lemon', 'okashi', 'all')
     * @param string $tagName 対象のタグ名 (デコード済み)
     * @return array|null タグ情報の配列 (PDO::FETCH_ASSOC)、または見つからなかった場合はnull
     */
    public function findBySiteIdAndName(string $siteId, string $tagName): ?array
    {
        try {
            $pdo = Database::getConnection();
            $params = [':tag_name' => $tagName];
            
            // ★修正: タグ名比較に COLLATE を適用
            $tagNameWhere = "name COLLATE utf8mb4_general_ci = :tag_name";

            if ($siteId === 'all') {
                $sql = "
                    SELECT 
                        id, 
                        name,
                        work_count, 
                        'all' AS site_id 
                    FROM 
                        tags 
                    WHERE 
                        {$tagNameWhere}
                    LIMIT 1;
                ";
            } else {
                // 特定サイトの場合: tag_sites テーブルをJOINして検索
                $params[':site_id'] = $siteId;
                
                // ★修正: サイトID比較に COLLATE を適用
                $siteIdWhere = "ts.site_id COLLATE utf8mb4_general_ci = :site_id";

                $sql = "
                    SELECT 
                        t.id, 
                        t.name, 
                        ts.site_id, 
                        ts.work_count
                    FROM 
                        tags t
                    INNER JOIN
                        tag_sites ts ON t.id = ts.tag_id
                    WHERE 
                        {$siteIdWhere} AND t.{$tagNameWhere}
                    LIMIT 1;
                ";
            }

            $stmt = $pdo->prepare($sql);
            
            // パラメータをバインド
            foreach ($params as $key => &$val) {
                 $stmt->bindParam($key, $val, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $tagInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            return $tagInfo ?: null;
        } catch (PDOException $e) {
            error_log("TagModel DB Error (findBySiteIdAndName): " . $e->getMessage());
            return null;
        }
    }

    /**
     * 特定の作品IDに紐づく全てのタグ名を取得する
     *
     * @param string $workId 対象の作品ID (例: 'ABC-123')
     * @return array タグ名の配列 (例: ['ビキニ', '巨乳', 'コスプレ'])
     */
    public function getTagsByWorkId(string $workId): array
    {
        try {
            $pdo = Database::getConnection();

            // ★修正: JOIN条件とWHERE条件に COLLATE を適用
            $sql = "
                SELECT 
                    t.name
                FROM 
                    tags t
                INNER JOIN 
                    work_tags wt ON t.id = wt.tag_id
                INNER JOIN 
                    works w ON w.work_id COLLATE utf8mb4_general_ci = wt.work_id  /* work_idの結合に適用 */
                WHERE 
                    w.work_id COLLATE utf8mb4_general_ci = :workId  /* work_idの比較に適用 */
                ORDER BY 
                    t.name ASC; 
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':workId', $workId, PDO::PARAM_STR);
            $stmt->execute();
            
            // FETCH_COLUMNでタグ名のみの配列を取得
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0); 

        } catch (PDOException $e) {
            error_log("TagModel DB Error (getTagsByWorkId): " . $e->getMessage());
            return []; // エラー時は空の配列を返す
        }
    }
}
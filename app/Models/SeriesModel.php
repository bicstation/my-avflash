<?php
// /home/wp552476/avflash.xyz/public_html/app/models/SeriesModel.php

namespace App\Models;

use App\Core\Database;
use PDO;
use PDOException;

class SeriesModel
{
    /**
     * 特定のサイトIDとシリーズ名に一致するシリーズ情報を取得する
     *
     * @param string $siteId 対象のサイトID
     * @param string $seriesName 対象のシリーズ名 (デコード済み)
     * @return array|null シリーズ情報の配列、または見つからなかった場合はnull
     */
    public function findBySiteIdAndName(string $siteId, string $seriesName): ?array
    {
        try {
            $pdo = Database::getConnection(); 
            
            // 照合順序を統一したため、シンプルでクリーンなSQLが利用可能
            $sql = "
                SELECT 
                    s.id, 
                    s.name, 
                    s.work_count, 
                    ss.site_id
                FROM 
                    series s
                INNER JOIN
                    series_sites ss ON s.id = ss.series_id 
                WHERE 
                    ss.site_id = :site_id AND s.name = :series_name
                LIMIT 1;
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':site_id', $siteId, PDO::PARAM_STR);
            $stmt->bindParam(':series_name', $seriesName, PDO::PARAM_STR);
            $stmt->execute();
            $seriesInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            return $seriesInfo ?: null;

        } catch (PDOException $e) {
            // エラーロギングとnull返却
            error_log("SeriesModel DB Error: " . $e->getMessage());
            return null; 
        }
    }

    /**
     * 特定の作品IDに紐づくシリーズ名を取得する
     * * シリーズが紐づいていない場合はnullを返す。
     *
     * @param string $workId 対象の作品ID (例: 'ABC-123')
     * @return string|null シリーズ名、または見つからなかった場合はnull
     */
    public function getSeriesNameByWorkId(string $workId): ?string
    {
        try {
            $pdo = Database::getConnection();

            $sql = "
                SELECT 
                    s.name
                FROM 
                    series s
                INNER JOIN 
                    work_series ws ON s.id = ws.series_id
                WHERE 
                    ws.work_id = :workId
                LIMIT 1;
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':workId', $workId, PDO::PARAM_STR);
            $stmt->execute();
            
            // 単一のカラム（シリーズ名）を文字列として取得
            $seriesName = $stmt->fetchColumn(); 

            // 結果が false (見つからなかった場合) の場合は null を返す
            return $seriesName !== false ? $seriesName : null;

        } catch (PDOException $e) {
            error_log("SeriesModel DB Error (getSeriesNameByWorkId): " . $e->getMessage());
            return null; // エラー時は null を返す
        }
    }
}
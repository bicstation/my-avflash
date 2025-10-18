<?php
// /home/wp552476/avflash.xyz/public_html/app/models/ActorModel.php

namespace App\Models;

use App\Core\Database;
use PDO;
use PDOException;

class ActorModel
{
    /**
     * 特定のサイトIDとアクター名に一致するアクター情報を取得する
     *
     * @param string $siteId 対象のサイトID
     * @param string $actorName 対象のアクター名 (デコード済み)
     * @return array|null アクター情報の配列、または見つからなかった場合はnull
     */
    public function findBySiteIdAndName(string $siteId, string $actorName): ?array
    {
        try {
            $pdo = Database::getConnection(); 
            
            $sql = "
                SELECT 
                    a.id, 
                    a.name, 
                    a.work_count, 
                    aa.site_id
                FROM 
                    actors a
                INNER JOIN
                    actor_sites aa ON a.id = aa.actor_id 
                WHERE 
                    aa.site_id = :site_id AND a.name = :actor_name
                LIMIT 1;
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':site_id', $siteId, PDO::PARAM_STR);
            $stmt->bindParam(':actor_name', $actorName, PDO::PARAM_STR);
            $stmt->execute();
            $actorInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            return $actorInfo ?: null;

        } catch (PDOException $e) {
            error_log("ActorModel DB Error: " . $e->getMessage());
            return null; 
        }
    }
}
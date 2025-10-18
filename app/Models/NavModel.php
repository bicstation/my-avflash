<?php
namespace App\Models;

use App\Core\Database;

class NavModel
{
    private \PDO $pdo;
    private const SIDEBAR_LIMIT = 20;

    public function __construct()
    {
        $this->pdo = Database::getConnection(); 
    }
    
    // ----------------------------------------------------
    // サイト別クエリ生成ロジック
    // ----------------------------------------------------

    /**
     * 俳優を作品数の多い順に取得する
     */
    public function getActors(string $siteId): array
    {
        $sql = "
            SELECT 
                m.name, 
                COUNT(wa.work_id) AS work_count
            FROM 
                actors m
            JOIN
                work_actors wa ON m.id = wa.actor_id
            JOIN
                works w ON wa.work_id = w.work_id
            WHERE 
                w.site_id = :siteId 
            GROUP BY 
                m.id, m.name
            ORDER BY 
                work_count DESC, m.name ASC
            LIMIT 
                :limit
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', self::SIDEBAR_LIMIT, \PDO::PARAM_INT);
        $stmt->bindValue(':siteId', $siteId, \PDO::PARAM_STR); 
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * タグを作品数の多い順に取得する
     */
    public function getTags(string $siteId): array
    {
        $sql = "
            SELECT 
                m.name, 
                COUNT(wt.work_id) AS work_count
            FROM 
                tags m
            JOIN
                work_tags wt ON m.id = wt.tag_id
            JOIN
                works w ON wt.work_id = w.work_id
            WHERE 
                w.site_id = :siteId 
            GROUP BY 
                m.id, m.name
            ORDER BY 
                work_count DESC, m.name ASC
            LIMIT 
                :limit
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', self::SIDEBAR_LIMIT, \PDO::PARAM_INT);
        $stmt->bindValue(':siteId', $siteId, \PDO::PARAM_STR); 
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * シリーズを作品数の多い順に取得する
     */
    public function getSeries(string $siteId): array
    {
        $sql = "
            SELECT 
                m.name, 
                COUNT(ws.work_id) AS work_count
            FROM 
                series m
            JOIN
                work_series ws ON m.id = ws.series_id
            JOIN
                works w ON ws.work_id = w.work_id
            WHERE 
                w.site_id = :siteId
            GROUP BY 
                m.id, m.name
            ORDER BY 
                work_count DESC, m.name ASC
            LIMIT 
                :limit
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', self::SIDEBAR_LIMIT, \PDO::PARAM_INT);
        $stmt->bindValue(':siteId', $siteId, \PDO::PARAM_STR); 
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * メーカー/ブランドを作品数の多い順に取得する
     */
    public function getBrands(string $siteId): array
    {
        $sql = "
            SELECT 
                m.name, 
                COUNT(wm.work_id) AS work_count
            FROM 
                brands m
            JOIN
                work_brands wm ON m.id = wm.brand_id
            JOIN
                works w ON wm.work_id = w.work_id
            WHERE 
                w.site_id = :siteId
            GROUP BY 
                m.id, m.name
            ORDER BY 
                work_count DESC, m.name ASC
            LIMIT 
                :limit
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', self::SIDEBAR_LIMIT, \PDO::PARAM_INT);
        $stmt->bindValue(':siteId', $siteId, \PDO::PARAM_STR); 
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * レーベルを作品数の多い順に取得する
     */
    public function getLabels(string $siteId): array
    {
        $sql = "
            SELECT 
                m.name, 
                COUNT(wl.work_id) AS work_count
            FROM 
                labels m
            JOIN
                work_labels wl ON m.id = wl.label_id
            JOIN
                works w ON wl.work_id = w.work_id
            WHERE 
                w.site_id = :siteId
            GROUP BY 
                m.id, m.name
            ORDER BY 
                work_count DESC, m.name ASC
            LIMIT 
                :limit
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', self::SIDEBAR_LIMIT, \PDO::PARAM_INT);
        $stmt->bindValue(':siteId', $siteId, \PDO::PARAM_STR); 
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdoInstance = null;
    private static array $config = [];

    /**
     * config.phpから設定を読み込む
     */
    public static function loadConfig(array $config): void
    {
        self::$config = $config['database'];
    }

    /**
     * PDOインスタンスを取得する（シングルトンパターン）
     */
    public static function getConnection(): PDO
    {
        if (self::$pdoInstance === null) {
            $c = self::$config;
            
            // configからcharsetを読み込む: 'charset=utf8mb4'
            $dsn = "{$c['driver']}:host={$c['host']};dbname={$c['dbname']};charset={$c['charset']}";

            try {
                // ★★★ 修正箇所: 接続オプションにPDO::MYSQL_ATTR_INIT_COMMANDを追加 ★★★
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    // MySQL接続直後に、クライアント側の文字セットと照合順序を統一するコマンドを強制実行
                    // エラーメッセージで混在が指摘されている照合順序の片方（ここではutf8mb4_unicode_ci）に統一します。
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];

                self::$pdoInstance = new PDO($dsn, $c['username'], $c['password'], $options);
            } catch (PDOException $e) {
                // データベース接続エラーは致命的なので、詳細をログに残すべき
                die("Database connection error: " . $e->getMessage());
            }
        }
        return self::$pdoInstance;
    }


    /**
     * クエリ実行ヘルパー
     * ... (以下、queryメソッドは変更なし)
     */
    public static function query(string $sql, array $params = []): array|bool
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);

        // 実行が成功したかどうかをチェック
        if (!$stmt->execute($params)) {
            // PDO::ERRMODE_EXCEPTIONを設定しているため、ここには通常到達しませんが、念のため
            return false;
        }

        // SELECT文の場合は結果を返す
        if (strtoupper(substr(trim($sql), 0, 6)) === 'SELECT') {
            return $stmt->fetchAll(); // 返り値の型は array
        }

        // INSERT/UPDATE/DELETEなどの場合は成功を返す
        return true; // 返り値の型は bool (これで赤線が消えます)
    }
}
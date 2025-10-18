<?php
// ===============================================
// 設定ファイル (config.php)
// ===============================================

// Composer Autoloadを読み込む
require __DIR__ . '/vendor/autoload.php';

// CSVファイルパス定義
$csv_files = [
    'lemon' => __DIR__ . '/data/lemon.csv',
    'okashi' => __DIR__ . '/data/okashi.csv',
    'b10f' => __DIR__ . '/data/b10f.csv',
];

// ★ 修正: APP_NAME 定数ではなく、配列 'app' 内で管理する
// if (!defined('APP_NAME')) {
//     define('APP_NAME', 'AV Flash Site'); 
// }
// print_r($csv_files);

return [
    // アプリケーション設定
    'app' => [
        'base_url' => '/',
        'name' => 'AVFLASH.XYZ', // サイト名はこちらで管理
        'debug' => false, // true: 開発環境 / false: 本番環境
    ],
    
    // データベース設定
    'database' => [
        'driver' => 'mysql', 
        'host' => 'localhost', 
        'dbname' => 'wp552476_avflash', 
        'username' => 'wp552476_avflash', 
        'password' => '1492nabe', 
        'charset' => 'utf8mb4',
        // 'collation' => 'utf8mb4_unicode_ci',
    ],
    
    // データ設定 (CSVファイルパスなど)
    'data' => [
        'csv_files' => $csv_files,
    ],
    
    // ★ ページネーション設定 (TagControllerで使用)
    'pagination' => [
        // 作品一覧で1ページあたりに表示する件数
        'works_per_page' => 20, 
    ],
];
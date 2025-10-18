<?php
// コマンドラインからの実行以外は拒否
if (PHP_SAPI !== 'cli') {
    die("Access denied. This script can only be run from the command line.\n");
}

// 絶対パスの基点を定義 (public_html ディレクトリ)
$baseDir = __DIR__;

// ------------------------------------
// 0. 環境設定とオートロード
// ------------------------------------
require $baseDir . '/vendor/autoload.php';

// ★★★ 修正箇所：すべての依存関係を手動で読み込む ★★★
require_once $baseDir . '/app/Core/Database.php';
require_once $baseDir . '/app/Controllers/DataController.php';

// ★★★ WorksModel.php の読み込みを追加 ★★★
require_once $baseDir . '/app/Models/WorksModel.php';
// ★★★ --------------------------------------- ★★★

// エラー報告を最大化
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 無制限の実行時間とメモリ設定 (CLI処理向け)
set_time_limit(0);
ini_set('memory_limit', '1G');

// ------------------------------------
// 1. 初期設定とコアファイルの読み込み
// ------------------------------------
global $config;
$config = require_once $baseDir . '/config.php';

// ★★★ use 宣言 ★★★
use App\Core\Database;
use App\Models\WorksModel; // WorksModel も使用するため追加
// ★★★ ----------- ★★★

// データベース接続クラスの準備
Database::loadConfig($config);

// ------------------------------------
// 2. コマンドライン引数の解析
// ------------------------------------
$command = $argv[1] ?? 'help';
// 変更: 'import' 後の引数 (CSVファイルパスのみ) が $args[0] に入る
$args = array_slice($argv, 2);

// ------------------------------------
// 3. コマンドの実行
// ------------------------------------
try {
    switch ($command) {
        case 'import':
            // 変更: 引数の検証 (CSVファイルパスが最低1つ必要)
            if (count($args) < 1) {
                echo "Usage: php cli.php import [csv_file_path]\n"; // helpメッセージを修正
                exit(1);
            }
            // 変更: csvFilePath のみを取得
            $csvFilePath = $args[0];

            $controllerClass = 'App\\Controllers\\DataController';

            if (class_exists($controllerClass)) {

                // ★★★ 修正 1：WorksModel をインスタンス化する ★★★
                $worksModel = new WorksModel();

                // ★★★ 修正 2：コントローラに WorksModel を引数として渡す ★★★
                $controller = new $controllerClass($worksModel);

                // ★★★ 修正 3：正しいメソッド名 (importData) を呼び出す ★★★
                if (method_exists($controller, 'importData')) {
                    // 変更: DataController側の引数に合わせて $csvFilePath のみ渡す
                    $controller->importData($csvFilePath);
                } else {
                    // ここでエラーが出た場合は、DataController.phpのメソッド名を確認
                    echo "Fatal Error: Method 'importData' not found in DataController.\n";
                }
            } else {
                 // ここでエラーが出たら、DataController.phpのファイル名や名前空間を確認
                 echo "Fatal Error: Class 'App\\Controllers\\DataController' not found. Check file path or namespace.\n";
            }
            break;

        case 'help':
        default:
            echo "Usage: php cli.php [command] [arguments]\n";
            echo "Commands:\n";
            // 変更: helpメッセージを修正
            echo " import [csv_file_path] - Imports data (e.g., ./data/okashi.csv).\n";
            break;
    }
} catch (\Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);
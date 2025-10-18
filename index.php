<?php
// /home/wp552476/avflash.xyz/public_html/index.php (修正版)

// 開発中はすべてのオリジンからのアクセスを許可
header('Access-Control-Allow-Origin: *'); 
// 許可するHTTPメソッド
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
// 許可するヘッダー
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// プリフライトリクエスト (OPTIONSメソッド) に対応
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


// ------------------------------------
// 0. Composer Autoloadの読み込み (必須)
// ------------------------------------
require __DIR__ . '/vendor/autoload.php';

// ------------------------------------
// 1. 初期設定と環境設定
// ------------------------------------
global $config;
$config = require_once __DIR__ . '/config.php'; 

// ★★★ 修正箇所: 手動でコアクラスを読み込む ★★★
require_once __DIR__ . '/app/Core/Database.php';   
require_once __DIR__ . '/app/Core/Router.php';      
// ★★★ --------------------------------------- ★★★


// ヘルパーの読み込み
require_once __DIR__ . '/app/helpers.php'; 

// クラスの利用宣言
use App\Core\Database;
use App\Core\Router;

// デバッグ設定
if (config('app.debug') === true) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
}

// ------------------------------------
// 2. DB接続の準備 (データ処理のために必須)
// ------------------------------------
Database::loadConfig($config); 

// Routerインスタンスの作成
$router = new Router();

// ------------------------------------
// 3. ルーティング定義の読み込みと実行
// ------------------------------------
require_once __DIR__ . '/web.php';

$uri = $_SERVER['REQUEST_URI'];
// ★ 修正: Router::findRoute() は [Controller, Method, [Args...]] の形式を返す
$routeData = Router::findRoute($uri); 

if (!$routeData) {
    http_response_code(404);
    \dd("404 Not Found: Could not find route for URI '{$uri}'"); 
}

// 4. コントローラーとアクションの実行 (引数対応)
// [0]: ControllerName, [1]: MethodName, [2]: Arguments Array
list($controllerName, $methodName, $args) = $routeData; // ★ 修正: $args (引数配列) を抽出

$controllerClass = 'App\\Controllers\\' . $controllerName; 

if (class_exists($controllerClass)) {
    $controller = new $controllerClass();
    
    if (method_exists($controller, $methodName)) {
        // ★ 修正: call_user_func_array を使用して引数を渡す
        // HomeController::index($siteId) や CategoryController::show($siteId, $categoryName) に対応
        call_user_func_array([$controller, $methodName], $args); 
        
    } else {
        \dd("500 Server Error: Method '{$methodName}' not found in '{$controllerClass}'.");
    }
} else {
    \dd("500 Server Error: Controller '{$controllerClass}' not found. Check filename/namespace.");
}
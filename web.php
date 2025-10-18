<?php
// ===============================================
// ルーティング定義リスト (web.php)
// ===============================================

/**
 * @var \App\Core\Router $router $router変数はindex.phpで定義されています
 */

// 1. トップページ / サイトトップ (既存のHTMLページ表示)
$router->get('/', 'HomeController@index');
$router->get('/{siteId}', 'HomeController@index');

// 2. 個別作品ページ (既存のHTMLページ表示)
$router->get('/work/{siteWorkId}', 'WorkController@show');


// ==========================================================
// 3. カテゴリ別作品一覧のルーティング (既存のHTMLページ表示)
// ==========================================================
// ... (既存のカテゴリールーティングを省略せずに続行) ...

$router->get('/tag/{tagName}', 'TagController@index');
$router->get('/series/{seriesName}', 'SeriesController@index');
$router->get('/actor/{actorName}', 'ActorController@index');
// ★ 新規追加: メーカー
$router->get('/manufacturer/{manufacturerName}', 'ManufacturerController@index');
// ★ 新規追加: レーベル
$router->get('/label/{labelName}', 'LabelController@index');


// ★ 特定サイトルート (URL例: /lemon/tag/ビキニ)
$router->get('/{siteId}/tag/{tagName}', 'TagController@index');
$router->get('/{siteId}/series/{seriesName}', 'SeriesController@index');
$router->get('/{siteId}/actor/{actorName}', 'ActorController@index');
// ★ 新規追加: メーカー
$router->get('/{siteId}/manufacturer/{manufacturerName}', 'ManufacturerController@index');
// ★ 新規追加: レーベル
$router->get('/{siteId}/label/{labelName}', 'LabelController@index');


// ==========================================================
// 4. ★★★ React連携のための API ルーティング定義（新規追加）★★★
// ==========================================================

// A. 作品詳細 API
// URL例: /api/work/{workId}
$router->get('/api/work/{siteWorkId}', 'WorkController@apiShow');

// 1. トップページ用 (サイトIDをデフォルトの'okashi'として扱う)
// URL: /api/works/latest
$router->get('/api/works/latest', 'WorkController@apiLatestList');

// 2. サイト別最新リスト
// URL: /api/{siteId}/works/latest
$router->get('/api/{siteId}/works/latest', 'WorkController@apiLatestList');

// B. カテゴリー別作品一覧 API
// URL例: /api/tag/{tagName} または /api/lemon/tag/{tagName}
$router->get('/api/tag/{tagName}', 'TagController@apiIndex');
$router->get('/api/series/{seriesName}', 'SeriesController@apiIndex');
$router->get('/api/actor/{actorName}', 'ActorController@apiIndex');
$router->get('/api/manufacturer/{manufacturerName}', 'ManufacturerController@apiIndex');
$router->get('/api/label/{labelName}', 'LabelController@apiIndex');

$router->get('/api/{siteId}/tag/{tagName}', 'TagController@apiIndex');
$router->get('/api/{siteId}/series/{seriesName}', 'SeriesController@apiIndex');
$router->get('/api/{siteId}/actor/{actorName}', 'ActorController@apiIndex');
$router->get('/api/{siteId}/manufacturer/{manufacturerName}', 'ManufacturerController@apiIndex');
$router->get('/api/{siteId}/label/{labelName}', 'LabelController@apiIndex');

<?php
// ===============================================
// グローバルヘルパー関数 (app/helpers.php)
// ===============================================

// グローバルで設定配列を保持 (config.phpで require_once される際に初期化される)
global $config;

/**
 * 設定値を取得する (Laravel風 config() 関数)
 * @param string $key (例: 'app.name', 'data.csv_files')
 * @return mixed
 */
function config(string $key): mixed {
    global $config;
    $keys = explode('.', $key);
    $value = $config;

    foreach ($keys as $k) {
        if (!is_array($value) || !isset($value[$k])) {
            return null;
        }
        $value = $value[$k];
    }
    return $value;
}

// ===============================================
// ビュー・テンプレート関数 (修正済み)
// ===============================================

// ===============================================
// ビュー・テンプレート関数 (修正版)
// ===============================================

/**
 * ビューをレンダリングする (コンテンツをバッファリングし、レイアウトに渡す)
 * @param string $viewPath (例: 'works.index' -> views/works/index.php)
 * @param array $data ビューに渡すデータ
 */
function view(string $viewPath, array $data = []): void {
    
    // ドット記法をパスに変換
    $contentPath = str_replace('.', '/', $viewPath); 
    
    // アプリケーションのルートディレクトリを取得 (index.phpがある場所を基準)
    // index.php から helpers.php が require されているなら、ROOT_PATH は index.php のディレクトリ
    // ただし、最も安全な方法は、ビューディレクトリまでの絶対パスを取得することです。
    
    // **重要な修正:** どこから呼び出されても、絶対パスでビューディレクトリを指定
    // ここでは、public_html/views/ をルートと仮定
    $viewDir = dirname(__DIR__) . '/views/'; // /public_html/views/ を指す
    
    // データを現在のスコープに展開
    extract($data, EXTR_OVERWRITE); 
    
    // ----------------------------------------------------------------
    // 1. メインコンテンツのバッファリング
    // ----------------------------------------------------------------
    ob_start();
    
    $fullContentPath = $viewDir . $contentPath . '.php'; // ★ 絶対パスで結合

    if (!file_exists($fullContentPath)) {
        // ビューファイルが存在しない場合はエラーを表示し、デバッグを助ける
        ob_end_clean(); // バッファを破棄
        http_response_code(500);
        dd("View file not found: " . $fullContentPath);
    }
    
    require $fullContentPath; 
    $content = ob_get_clean();
    
    // ----------------------------------------------------------------
    // 2. レイアウトへの引き渡しとレンダリング
    // ----------------------------------------------------------------
    extract(['content' => $content], EXTR_OVERWRITE);

    // レイアウトファイルも絶対パスで指定
    $layoutPath = $viewDir . 'layouts/app.php'; // ★ 絶対パスで結合
    
    if (!file_exists($layoutPath)) {
        http_response_code(500);
        dd("Layout file not found: " . $layoutPath);
    }

    require $layoutPath; 
}

/**
 * 特定のビューパーツ（テンプレートの一部）を読み込む
 * この関数は、コンテンツがバッファリングされるようになったため、不要になります。
 */
function get_template_part(string $partPath): void {
    // 実行されなくなります
    require __DIR__ . '/../views/' . $partPath . '.php';
}


// ===============================================
// デバッグ関数
// ===============================================

/**
 * 変数の内容を整形して表示し、処理を終了する (Dump and Die)
 */
function dd($var): void {
    echo "<pre style='background: #333; color: #fff; padding: 15px; border: 1px solid #000; z-index: 9999; font-size: 14px;'>";
    var_dump($var);
    echo "</pre>";
    exit;
}

/**
 * 変数の内容を整形して表示する (Dump)
 */
function dump($var): void {
    echo "<pre style='background: #f0f0f0; color: #000; padding: 10px; border: 1px solid #ccc; z-index: 9998; font-size: 12px;'>";
    var_dump($var);
    echo "</pre>";
}


<?php

namespace App\Controllers;

use App\Models\WorksModel;
use App\Controllers\NavController;
use App\Models\TagModel;
use App\Models\SeriesModel;

class WorkController
{
    /**
     * 単一の作品詳細ページを表示する
     * URL: /work/{workId}
     * @param string $workId 作品ID (例: 'ABC-123')
     */
    public function show(string $siteWorkId)
    {
        // ★★★ 共通ロジック 1: URL引数の分解とチェック ★★★
        $parts = explode('-', $siteWorkId, 2);

        if (count($parts) !== 2 || empty($parts[0]) || empty($parts[1])) {
            // 不正な形式の場合は404エラー
            http_response_code(404);
            \dd("404 Not Found: Invalid URL format '{$siteWorkId}'. Expected siteId-workId.");
        }

        $siteId = $parts[0];
        $workId = $parts[1]; // Modelに渡す作品ID

        $worksModel = new WorksModel();
        $tagModel = new TagModel(); // ★★★ この行を追加！ ★★★
        $seriesModel = new SeriesModel(); // ★★★ この行を追加！ ★★★

        // 1. 作品の詳細データを取得
        // WorksModel::getWorkDetails(string $workId) は works と work_affiliates を結合して取得
        $work = $worksModel->getWorkDetails($siteId, $workId); // [cite: 32]

        if (!$work) {
            // 作品が見つからない場合は404エラー
            http_response_code(404);
            \dd("404 Not Found: Work ID '{$workId}' does not exist.");
        }

        // 2. 関連データの取得と結合 (★★★ 今回の主要な追加ロジック ★★★)

        // 2-1. 女優（アクター）名の取得
        // WorksModel::getActorsByWorkIds は、[workId => 'Name1, Name2'] のマップを返す [cite: 91]
        $actorMap = $worksModel->getActorsByWorkIds([$workId]); // 配列で渡す

        // 取得したアクター名を作品データに結合
        $actorNamesString = $actorMap[$workId] ?? null;
        if ($actorNamesString) {
            // 例: '八木奈々, 中井ゆかり' を配列に変換してビューで扱いやすくする
            $work['actor_list'] = array_map('trim', explode(',', $actorNamesString));
        } else {
            $work['actor_list'] = [];
        }

        // TODO: シリーズ、タグデータの取得
        // / 2-2. タグデータの取得 (★ New)
        $work['tag_list'] = $tagModel->getTagsByWorkId($workId); // 例: ['ビキニ', '巨乳']

        // 2-3. シリーズ名の取得 (string または null) ★★★ New ★★★
        $seriesName = $seriesModel->getSeriesNameByWorkId($workId);

        // シリーズが紐づいていれば work 配列に格納 (空の場合は null を格納)
        $work['series_name'] = $seriesName;

        // 3. サイドバーデータの取得
        $siteId = $work['site_id'];
        $navController = new NavController();
        $sidebarData = $navController->getSidebarData($siteId);

        // 4. Viewにデータを渡してレンダリング
        view('works/show', [
            'title' => $work['title'] . ' | 詳細',
            'work' => $work,
            'siteId' => $siteId,
            'sidebarData' => $sidebarData,
            // 'actorList' => $work['actor_list'], // work配列内に含めたため不要
        ]);
    }

    // ========================================================
    // ★★★ 新規追加: API専用メソッド (React用) ★★★
    // URL: /api/work/{workId} に対応
    // ========================================================
    public function apiShow(string $siteWorkId): void
    {
        // ★★★ 共通ロジック 1: URL引数の分解とチェック ★★★
        $parts = explode('-', $siteWorkId, 2);

        if (count($parts) !== 2 || empty($parts[0]) || empty($parts[1])) {
            header('Content-Type: application/json; charset=UTF-8');
            http_response_code(400); // 400 Bad Request
            echo json_encode(['status' => 'error', 'message' => "Invalid URL format. Expected siteId-workId."], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $siteId = $parts[0];
        $workId = $parts[1]; // Modelに渡す作品ID


        // 1. モデルをメソッド内でインスタンス化する (既存の show メソッドと同じパターン)
        $worksModel = new WorksModel();
        $tagModel = new TagModel();
        $seriesModel = new SeriesModel();

        // 2. 作品の詳細データを取得
        // WorksModel::getWorkDetails(string $workId) は works と work_affiliates を結合して取得
        $workData = $worksModel->getWorkDetails($siteId, $workId);

        // 3. データが見つからない場合の処理 (404エラーをJSONで返す)
        if (empty($workData)) {
            header('Content-Type: application/json; charset=UTF-8');
            http_response_code(404);
            // データを配列に格納し、JSONとして出力
            echo json_encode(['status' => 'error', 'message' => "404 Not Found: Work ID '{$workId}' does not exist."], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 4. 関連データの取得と結合 (既存の show メソッドのロジックを再利用)

        // 4-1. 女優（アクター）名の取得
        // WorksModel::getActorsByWorkIds は、[workId => 'Name1, Name2'] のマップを返す
        $actorMap = $worksModel->getActorsByWorkIds([$workId]);

        $actorNamesString = $actorMap[$workId] ?? null;
        if ($actorNamesString) {
            // 例: '八木奈々, 中井ゆかり' を配列に変換してJSONで扱いやすくする
            $workData['actor_list'] = array_map('trim', explode(',', $actorNamesString));
        } else {
            $workData['actor_list'] = [];
        }

        // 4-2. タグデータの取得
        $workData['tag_list'] = $tagModel->getTagsByWorkId($workId); // 例: ['ビキニ', '巨乳']

        // 4-3. シリーズ名の取得
        $seriesName = $seriesModel->getSeriesNameByWorkId($workId);
        $workData['series_name'] = $seriesName;

        // 4-4. (サイドバーデータなど、HTMLレンダリングにしか使わないデータは取得しない)


        // 5. データをJSON形式にエンコードして出力 (HTTPステータスコード 200 OK)
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(200);

        $response = [
            'status' => 'success',
            'data'   => $workData, // 整形されたリッチな作品データ
        ];

        // JSON_UNESCAPED_UNICODE を使って日本語を正しく出力
        echo json_encode($response, JSON_UNESCAPED_UNICODE);

        // Controllerの処理をここで強制終了 (JSON出力後に余計な文字が出ないようにする)
        exit;
    }

// WorkController.php 内

    /**
     * 最新作品リストをJSONで返す (トップページまたはサイト別一覧用)
     * URL: /api/works/latest または /api/{siteId}/works/latest
     * @param string $siteId サイトID ('okashi'など。ルーティングから渡される)
     */
    public function apiLatestList(string $siteId = 'okashi'): void
    {
        $worksModel = new WorksModel();
        
        // 1. まずはDBから作品の基本情報リストを取得
        // (actor_list, tag_listなどは含まれていない状態)
        $workList = $worksModel->getLatestWorkList($siteId, 20);

        // ★ 2. 関連データ（女優、タグ、シリーズ）を結合するための処理
        $processedWorkList = [];
        
        // 取得した作品リストを一つずつループし、詳細データを付与する
        foreach ($workList as $work) {
            // WorkModelに、基本データに関連データを結合する新しいメソッドを呼び出す
            // このメソッドは次のステップでWorksModelに定義します
            $processedWork = $worksModel->getWorkDataWithRelations($work); 

            // 結合後のデータを新しいリストに追加
            if ($processedWork) {
                $processedWorkList[] = $processedWork;
            }
        }

        // 3. JSON出力
        header('Content-Type: application/json; charset=UTF-8');

        if (!empty($processedWorkList)) {
            http_response_code(200);
            // ★ 関連データ結合後のリスト ($processedWorkList) を出力
            echo json_encode(['status' => 'success', 'data' => $processedWorkList], JSON_UNESCAPED_UNICODE);
        } else {
            // データがない場合も空の配列を返す
            http_response_code(200);
            echo json_encode(['status' => 'success', 'data' => []], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
}

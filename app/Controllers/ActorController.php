<?php
// /home/wp552476/avflash.xyz/public_html/app/controllers/ActorController.php

namespace App\Controllers;

use App\Models\WorksModel;
use App\Models\ActorModel; // ★ ActorModelを使用
use App\Core\Config;
use App\Controllers\NavController;

class ActorController
{
    public function index(string $siteId, string $actorName) // ★ $actorNameを使用
    {
        // 1. 初期設定とURLデコード
        $perPage = Config::get('pagination.works_per_page', 20);
        
        $currentPage = isset($_GET['page']) && is_numeric($_GET['page']) && (int)$_GET['page'] >= 1
                     ? (int)$_GET['page']
                     : 1;
                     
        $offset = ($currentPage - 1) * $perPage;

        // Routerから渡される $actorName はエンコードされている可能性があるため、デコード
        $decodedActorName = urldecode($actorName);

        $worksModel = new WorksModel();
        $actorModel = new ActorModel(); // ★ ActorModelを使用

        // 2. アクター情報の取得と存在チェック (ActorModel::findBySiteIdAndName)
        $actorInfo = $actorModel->findBySiteIdAndName($siteId, $decodedActorName);

        if (!$actorInfo) {
            // アクターが見つからなかった場合の処理
            die("404 Not Found: Actor '{$decodedActorName}' not found for site '{$siteId}'");
        }

        // 3. 作品データの取得 (WorksModel::getWorksByActor)
        $works = $worksModel->getWorksByActor($siteId, $decodedActorName, $perPage, $offset); // ★ メソッド変更
        
        // dd($works);

        // 4. 総作品数の取得とページネーションの計算 (WorksModel::getTotalWorksCountByActor)
        $totalWorks = $worksModel->getTotalWorksCountByActor($siteId, $decodedActorName); // ★ メソッド変更
        $totalPages = ceil($totalWorks / $perPage);

        // 5. サイドバーデータの取得
        $navController = new NavController();
        $sidebarData = $navController->getSidebarData($siteId); 
        
        // 6. Viewにデータを渡してレンダリング
        // ★ series/index を actor/index (新規作成を推奨) または works/index に渡す
        view('actor/index', [ 
            'title' => strtoupper($siteId) . ' | アクター: ' . $decodedActorName . ' の作品一覧', 
            'siteId' => $siteId,
            'actorName' => $decodedActorName,
            'actorInfo' => $actorInfo,
            'works' => $works,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'sidebarData' => $sidebarData,
            
            // ページネーションコンポーネント用の変数
            'isActorPage' => true,                   // ★ 新規追加
            'actorNameUrl' => $actorName,            // ★ 新規追加
        ]);
    }
}
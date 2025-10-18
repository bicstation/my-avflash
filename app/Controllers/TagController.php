<?php
// /home/wp552476/avflash.xyz/public_html/app/controllers/TagController.php

namespace App\Controllers;

use App\Models\WorksModel;
use App\Models\TagModel;
use App\Controllers\NavController;
use App\Core\Config; 

class TagController
{
    /**
     * 特定のサイトの、特定のタグに紐づく作品一覧を表示するアクション
     */
    public function index(string $arg1, ?string $arg2 = null)
    {
        // ★修正点: 引数の数に応じて $siteId と $tagName を決定
        if ($arg2 === null) {
            // パターン2: /tag/{tagName} の場合
            $siteId = 'all';
            $tagName = $arg1;
        } else {
            // パターン1: /{siteId}/tag/{tagName} の場合
            $siteId = $arg1;
            $tagName = $arg2;
        }

        // 1. 初期設定とURLデコード
        $perPage = Config::get('pagination.works_per_page', 20);
        
        $currentPage = isset($_GET['page']) && is_numeric($_GET['page']) && (int)$_GET['page'] >= 1
                     ? (int)$_GET['page']
                     : 1;
                     
        $offset = ($currentPage - 1) * $perPage;

        // Routerから渡される $tagName はエンコードされている可能性があるため、デコード
        $decodedTagName = urldecode($tagName);

        $worksModel = new WorksModel();
        $tagModel = new TagModel();

        // 2. タグ情報の取得と存在チェック (TagModel::findBySiteIdAndName は別途修正が必要です)
        $tagInfo = $tagModel->findBySiteIdAndName($siteId, $decodedTagName);

        if (!$tagInfo) {
            die("404 Not Found: Tag '{$decodedTagName}' not found for site '{$siteId}'");
        }

        // 3. 作品データの取得
        $works = $worksModel->getWorksByTag($siteId, $decodedTagName, $perPage, $offset);
        
        // 4. 総作品数の取得とページネーションの計算
        $totalWorks = $worksModel->getTotalWorksCountByTag($siteId, $decodedTagName);
        $totalPages = ceil($totalWorks / $perPage);

        // 5. サイドバーデータの取得
        $navController = new NavController();
        $sidebarData = $navController->getSidebarData($siteId); 
        
        // 6. Viewにデータを渡してレンダリング
        view('tag/index', [
            'title' => strtoupper($siteId) . ' | タグ: ' . $decodedTagName . ' の作品一覧', 
            'siteId' => $siteId,
            'tagName' => $decodedTagName,
            'tagInfo' => $tagInfo, 
            'works' => $works,
            'currentPage' => $currentPage, 
            'totalPages' => $totalPages,
            'sidebarData' => $sidebarData,
            
            'tagNameUrl' => $tagName, 
        ]);
    }
}
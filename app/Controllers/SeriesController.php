<?php
// /home/wp552476/avflash.xyz/public_html/app/controllers/SeriesController.php

namespace App\Controllers;

use App\Models\WorksModel;
use App\Models\SeriesModel;
use App\Core\Config;
use App\Controllers\NavController;

class SeriesController
{
    public function index(string $siteId, string $seriesName)
    {
        // 1. 初期設定とURLデコード
        $perPage = Config::get('pagination.works_per_page', 20);
        
        $currentPage = isset($_GET['page']) && is_numeric($_GET['page']) && (int)$_GET['page'] >= 1
                     ? (int)$_GET['page']
                     : 1;
                     
        $offset = ($currentPage - 1) * $perPage;

        // Routerから渡される $seriesName はエンコードされている可能性があるため、デコード
        $decodedSeriesName = urldecode($seriesName);

        $worksModel = new WorksModel();
        $seriesModel = new SeriesModel();

        // 2. シリーズ情報の取得と存在チェック
        $seriesInfo = $seriesModel->findBySiteIdAndName($siteId, $decodedSeriesName);

        if (!$seriesInfo) {
            die("404 Not Found: Series '{$decodedSeriesName}' not found for site '{$siteId}'");
        }

        // 3. 作品データの取得
        $works = $worksModel->getWorksBySeries($siteId, $decodedSeriesName, $perPage, $offset);
        
        // 4. 総作品数の取得とページネーションの計算
        $totalWorks = $worksModel->getTotalWorksCountBySeries($siteId, $decodedSeriesName);
        $totalPages = ceil($totalWorks / $perPage);

        // 5. サイドバーデータの取得
        $navController = new NavController();
        $sidebarData = $navController->getSidebarData($siteId); 
        
        // 6. Viewにデータを渡してレンダリング
        view('series/index', [
            'title' => strtoupper($siteId) . ' | シリーズ: ' . $decodedSeriesName . ' の作品一覧', 
            'siteId' => $siteId,
            'seriesName' => $decodedSeriesName,
            'seriesInfo' => $seriesInfo,
            'works' => $works,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'sidebarData' => $sidebarData,
            
            // ★ 修正点1: tagNameUrl を seriesNameUrl に変更
            'seriesNameUrl' => $seriesName, // URLエンコード済みのシリーズ名を渡す
            // ★ 修正点2: isSeriesPage = true を追加し、シリーズページであることを明示
            'isSeriesPage' => true, 
        ]);
    }
}
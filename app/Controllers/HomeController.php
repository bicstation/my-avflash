<?php
// /home/wp552476/avflash.xyz/public_html/app/controllers/HomeController.php

namespace App\Controllers;

use App\Models\WorksModel;
use App\Controllers\NavController;

class HomeController
{
    /**
     * トップページ (作品一覧) を表示するアクション
     * URL: /{siteId} または / (ルーティングで処理)
     * @param string $siteId 現在閲覧中のサイトID。 デフォルトは 'okashi' を表示する。
     */
    public function index(string $siteId = 'okashi') // ★ 変更1: デフォルトを 'okashi' に変更
    {
        // 1. 初期設定
        $perPage = 30; // 1ページあたりの表示件数
        // GETパラメータからページ番号を取得。未指定の場合は1。
        $page = $_GET['page'] ?? 1;
        $offset = ($page - 1) * $perPage;

        // モデルのインスタンス化
        $worksModel = new WorksModel();
        
        // 2. 作品データの取得 (特定サイトのロジックに統一)
        // ★ 変更2: 'all' の分岐を削除
        
        // 特定サイトの場合（lemon, okashi, b10f, またはデフォルトの okashi）
        $works = $worksModel->getLatestWorks($siteId, $perPage, $offset);
        $totalWorks = $worksModel->getTotalWorksCount($siteId);
        $titlePrefix = strtoupper($siteId);
        // dd($works);
        $totalPages = ceil($totalWorks / $perPage);

        // 3. サイドバーデータの取得とメタ情報の決定
        $navController = new NavController();
        
        // NavControllerは 'all' 処理を削除済みなので、そのまま $siteId を渡す
        $sidebarData = $navController->getSidebarData($siteId); 
        $sidebarMeta = $navController->getSidebarMeta($siteId);

        // dd($sidebarData);

        // 4. Viewにデータを渡してレンダリング
        // view() 関数はグローバル関数か、基底コントローラーで定義されているものと仮定
        view('home/index', [
            'title' => $titlePrefix . ' | 最新作品一覧', 
            'currentSiteId' => $siteId, // 統一された変数名
            'works' => $works,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'sidebarData' => $sidebarData,
            'sidebarMeta' => $sidebarMeta,
        ]);
    }
}
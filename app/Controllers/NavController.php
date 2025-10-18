<?php

namespace App\Controllers;

use App\Models\NavModel;

class NavController
{
    private NavModel $navModel;

    public function __construct()
    {
        $this->navModel = new NavModel();
    }

    /**
     * 指定されたサイトIDに基づいて、サイドメニューに表示するすべての動的データを取得する
     */
    /**
     * 指定されたサイトIDに基づいて、サイドメニューに表示するすべての動的データを取得する
     */
    public function getSidebarData(string $siteId): array
    {
        // グローバルな設定変数からデバッグフラグを取得すると仮定
        // 実際のアプリケーション構造に合わせて読み替えが必要
        $isDebug = $GLOBALS['config']['app']['debug'] ?? false;

        // デバッグモードがONの場合にのみdumpを実行
        if ($isDebug) {
            dump('--- サイトIDの確認 ---');
            dump('$siteId: ' . $siteId);
        }

        // NavModelの依存性の注入（Constructorでの注入を推奨）
        // $this->navModel は Controllerのプロパティとして事前にセットされているものとします

        $actors = $this->navModel->getActors($siteId);
        if ($isDebug) {
            dump('--- 俳優データ (getActors) ---');
            dump($actors);
        }

        $tags = $this->navModel->getTags($siteId);
        if ($isDebug) {
            dump('--- タグデータ (getTags) ---');
            dump($tags); // ★次にこの出力が表示されるか確認してください
        }

        $series = $this->navModel->getSeries($siteId);
        if ($isDebug) {
            dump('--- シリーズデータ (getSeries) ---');
            dump($series);
        }

        $brands = $this->navModel->getBrands($siteId);
        if ($isDebug) {
            dump('--- ブランドデータ (getBrands) ---');
            dump($brands);
        }

        $labels = $this->navModel->getLabels($siteId);
        if ($isDebug) {
            dump('--- レーベルデータ (getLabels) ---');
            dump($labels);
        }

        if ($isDebug) {
            dump('--- デバッグ終了 ---');
        }

        return [
            'actors'        => $actors,
            'tags'          => $tags,
            'series'        => $series,
            'brands'        => $brands,
            'labels'        => $labels,
        ];
    }

    /**
     * サイトIDに基づいてサイドバーのメタ情報（タイトルや静的カテゴリー）を取得する
     */
    public function getSidebarMeta(string $siteId): array
    {
        // 1. サイドバータイトル (sidebarTitle) の決定
        $sidebarTitle = match ($siteId) {
            'okashi' => '💖 アイドル委員会',
            'lemon'  => '🎥 一般向人気作品',
            'b10f'   => '🎬 独占マニアック',
            default  => '人気コンテンツ', // ★ 不正なIDが渡された場合のデフォルトタイトル
        };

        // 2. 静的なカテゴリー (staticCategories) の決定
        $staticCategories = match ($siteId) {
            'lemon', 'b10f' => [ // 'all' を削除
                // $siteId を使ってリンクを生成
                ['name' => '無修正', 'icon' => 'fas fa-ban', 'href' => '/' . $siteId . '/category/uncensored'],
                ['name' => '独占配信', 'icon' => 'fas fa-crown', 'href' => '/' . $siteId . '/category/exclusive'],
                ['name' => '人気女優', 'icon' => 'fas fa-venus-mars', 'href' => '/' . $siteId . '/category/actress'],
            ],
            // 'okashi' または default の場合、okashi のカテゴリー構造を適用
            default => [
                ['name' => 'ドラマ・映画', 'icon' => 'fas fa-film', 'href' => '/' . $siteId . '/category/movie'],
                ['name' => 'アニメ', 'icon' => 'fas fa-mask', 'href' => '/' . $siteId . '/category/anime'],
                ['name' => 'バラエティ', 'icon' => 'fas fa-tv', 'href' => '/' . $siteId . '/category/variety'],
            ],
        };

        return [
            'sidebarTitle' => $sidebarTitle,
            'staticCategories' => $staticCategories,
        ];
    }
}

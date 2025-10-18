<?php

namespace App\Core;

/**
 * 設定値へのアクセスを提供する静的ユーティリティクラス
 */
class Config
{
    /**
     * @var array アプリケーション全体の設定を保持する配列
     */
    private static array $settings = [];

    /**
     * config.php ファイルから設定を読み込み、静的プロパティに格納する
     *
     * @param array $config config.phpが返す連想配列
     */
    public static function load(array $config): void
    {
        self::$settings = $config;
    }

    /**
     * 設定キーを指定して値を取得する
     * ネストされたキーはドット記法 (例: 'database.dbname') でアクセス可能
     *
     * @param string $key 取得したい設定キー
     * @param mixed $default キーが存在しない場合に返すデフォルト値
     * @return mixed 設定値、またはデフォルト値
     */
    public static function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = self::$settings;

        foreach ($keys as $k) {
            if (is_array($value) && array_key_exists($k, $value)) {
                $value = $value[$k];
            } else {
                return $default; // キーが見つからなかった場合はデフォルト値を返す
            }
        }

        return $value;
    }
}
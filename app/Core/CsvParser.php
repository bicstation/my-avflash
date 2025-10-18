<?php
namespace App\Core;

class CsvParser
{
    /**
     * CSVファイルからデータを読み込み、連想配列の配列として返す
     * * @param string $filePath 読み込むCSVファイルの絶対パス
     * @param string $delimiter 区切り文字 (通常はカンマ ',')
     * @return array CSVデータの連想配列の配列
     * @throws \Exception ファイルが見つからない、または読み込めない場合
     */
    public static function parse(string $filePath, string $delimiter = ','): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception("CSV file not found or unreadable: " . $filePath);
        }

        $data = [];
        // ファイルポインタを開く
        if (($handle = fopen($filePath, 'r')) !== false) {
            
            // 1. ヘッダー行を読み込む
            $headers = fgetcsv($handle, 0, $delimiter); // 0は最大行長なし
            if ($headers === false) {
                fclose($handle);
                return $data;
            }
            
            // ヘッダーのクリーンアップ: 空白・全角スペースのトリム
            // CSVヘッダーの「木下凛々子  お綺麗ですね隣の奥様 凛々子奥様編」のような全角スペースを処理するため、mb_convert_kanaを使用します
            $headers = array_map(function($h) {
                // 全角スペースを半角に変換し、両端の空白をトリム
                $cleaned = trim(mb_convert_kana($h, 's'));
                // CSV内の不必要なダブルクォートやNULL文字も取り除く
                return str_replace(['"', "\0"], '', $cleaned); 
            }, $headers);

            // 2. データ行を読み込む
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                // 行データにもクリーンアップを適用 (特にHTMLリンクの末尾などに含まれる余計な空白を処理)
                $row = array_map('trim', $row);

                // ヘッダー数とカラム数が一致しない行はスキップ（CSVの破損対策）
                if (count($headers) !== count($row)) {
                    // エラーログに出力し、処理を続行
                    error_log("Row skipped due to mismatched column count in CSV: " . $filePath);
                    continue; 
                }
                
                // ヘッダーと行データを結合して連想配列を作成
                $data[] = array_combine($headers, $row);
            }
            
            fclose($handle);
        }

        return $data;
    }
}
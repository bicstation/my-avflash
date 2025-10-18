public_html 以下を　一つにまとめる　コマンド
find . -type f \( -name "*.php" -o -name "*.htaccess" -o -name "*.js" -o -name "*.css" \) -exec sh -c 'echo -e "\n\n--- FILE: {} ---\n"; cat {};' \; >> structure.txt


CSVファイルは、そのままアップデートする。
インポートは
public_html以下で、
php cli.php import ./data/okashi.csv
php cli.php import ./data/lemon.csv
php cli.php import ./data/b10f.csv



TRUNCATE TABLE works;
TRUNCATE TABLE work_affiliates;
TRUNCATE TABLE work_captures;
TRUNCATE TABLE work_actors;
TRUNCATE TABLE work_tags;
TRUNCATE TABLE work_brands;
TRUNCATE TABLE work_labels;
TRUNCATE TABLE work_series;

-- マスタデータテーブル（IDをリセットしてクリーンアップ）
TRUNCATE TABLE actors;
TRUNCATE TABLE tags;
TRUNCATE TABLE series;
TRUNCATE TABLE labels;
TRUNCATE TABLE brands; 

-- サイトごとの関連付けや補助テーブル
TRUNCATE TABLE actor_sites;
TRUNCATE TABLE series_sites;
TRUNCATE TABLE tag_sites;
TRUNCATE TABLE brands_sites;

-- 過去のデバッグ中に誤って使用された可能性のあるテーブルもクリア
TRUNCATE TABLE work_brands;
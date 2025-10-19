// src/app/page.tsx

import WorkCard from "@/components/WorkCard";
import Sidebar from "@/components/Sidebar"; 
import Breadcrumb from "@/components/Breadcrumb";

// next.config.tsで設定した環境変数を取得
// Next.jsサーバーは、この完全な外部URLに直接アクセスします。
const API_BASE_URL = process.env.API_BASE_URL;

// APIから取得する作品データの型定義
// APIのJSONフィールド名に合わせて定義します。
interface Work {
  id: number;
  title: string;
  release_date: string;
  cover_url: string;   // 画像URL (仮定)
  price: number;       // 価格 (仮定)
  product_url: string; // アフィリエイトリンク (仮定)
  brand_name: string;  // 仕訳（ブランド名）(仮定)
}

// APIの応答全体を表すインターフェース
interface ApiResponse {
    status: string; // 例: "success"
    data: Work[];
}


/**
 * サーバーコンポーネントとして動作し、サーバーサイドでデータを取得します。
 */
async function getLatestWorks(): Promise<Work[]> {
  if (!API_BASE_URL) {
    console.error("API_BASE_URLが設定されていません。next.config.tsを確認してください。");
    return [];
  }

  const url = `${API_BASE_URL}/works/latest`; 
  
  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
      cache: 'no-store' 
    });

    if (!response.ok) {
      throw new Error(`API fetch failed with status: ${response.status} from ${url}`);
    }

    const apiResponse: ApiResponse = await response.json(); 
    
    // API応答から作品データの配列 (data) のみを取り出して返却
    return apiResponse.data; 

  } catch (error) {
    console.error("作品データの取得に失敗しました:", error);
    return [];
  }
}


export default async function HomePage() {
  const latestWorks = await getLatestWorks();

  return (
    <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
      
      {/* 1. サイドバー (左側: LGサイズ以上で3/12幅) */}
      <aside className="lg:col-span-3">
        <Sidebar />
      </aside>
      
      {/* 2. メインコンテンツ (右側: LGサイズ以上で9/12幅) */}
      <div className="lg:col-span-9">
        
        {/* パンくずリスト */}
        <Breadcrumb items={[{ label: 'ホーム', href: '/' }, { label: 'コンテンツ', href: '#' }]} />

        {/* コンテンツヘッダー (スクショの「コンテンツ」部分) */}
        <div className="bg-gray-800 text-white p-4 rounded-t-lg mt-0">
          <h2 className="text-xl font-semibold">コンテンツ</h2>
        </div>
        
        {/* 作品一覧コンテナ (スクショの「最新作品一覧」部分) */}
        <div className="bg-white p-4 sm:p-6 rounded-b-lg shadow-xl">
            <h3 className="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">最新作品一覧</h3>

            {/* 作品カードリスト */}
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            {latestWorks.length > 0 ? (
                latestWorks.map((work) => (
                <WorkCard key={work.id} work={work} />
                ))
            ) : (
                <div className="col-span-full p-6 bg-red-50 border border-red-300 text-red-700 rounded-lg">
                作品データがありません。APIの稼働状況を確認してください。
                </div>
            )}
            </div>
        </div>
      </div>
    </div>
  );
}
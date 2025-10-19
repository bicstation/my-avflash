// src/app/page.tsx (ローカルPCでこの内容で上書き)

// next.config.tsで設定した環境変数を取得
// Next.jsサーバーは、この完全な外部URLに直接アクセスする
const API_BASE_URL = process.env.API_BASE_URL;

// APIから取得するデータの型定義（そのまま）
interface Work {
  id: number;
  title: string;
  date: string;
}


/**
 * サーバーコンポーネントとして動作し、サーバーサイドでデータを取得します。
 */
async function getLatestWorks(): Promise<Work[]> {
  if (!API_BASE_URL) {
    console.error("API_BASE_URLが設定されていません。next.config.tsを確認してください。");
    return [];
  }

  // APIの完全なURL
  const url = `${API_BASE_URL}/works/latest`; 
  
  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
      // cache: 'no-store' 
      cache: 'no-store' 
    });

    if (!response.ok) {
      // エラーメッセージでレスポンスステータスを表示
      throw new Error(`API fetch failed with status: ${response.status} from ${url}`);
    }
    
    // JSONのパースと返却
    const { data } = await response.json(); 
    
    // 取得したデータはPHP APIのJSON形式を想定しています
    // ここでJSONのトップレベルが "data" フィールドを持つと仮定して修正します
    // 実際に取得されたJSON: {"status":"success","data":[{"id":...}]}
    return data; 

  } catch (error) {
    console.error("作品データの取得に失敗しました:", error);
    return [];
  }
}


export default async function HomePage() {
  // サーバーサイドでデータ取得を実行
  const latestWorks = await getLatestWorks();

  return (
    <main className="flex min-h-screen flex-col items-center p-24">
      <h1 className="text-3xl font-bold mb-8">Next.js + PHP API連携デモ</h1>
      <p className="mb-4 text-gray-600">このデータはレンタルサーバーのAPI (wp552476.wpx.jp) から取得されています。</p>
      
      {latestWorks.length > 0 ? (
        <div className="w-full max-w-xl">
          <h2 className="text-xl font-semibold mb-4">最新作品 ({latestWorks.length}件)</h2>
          <ul className="space-y-3">
            {latestWorks.map((work) => (
              <li key={work.id} className="p-4 bg-gray-50 border rounded-lg shadow-sm hover:bg-gray-100 transition">
                <p className="font-medium text-lg">{work.title}</p>
                <p className="text-sm text-gray-500">公開日: {work.date}</p>
              </li>
            ))}
          </ul>
        </div>
      ) : (
        <p className="text-red-500 p-4 border border-red-300 bg-red-50 rounded-lg">
          作品データがありません。APIの稼働状況を確認してください。
        </p>
      )}

      <div className="mt-12 text-center text-sm text-blue-600">
        <a href="https://nextjs.org/docs" target="_blank" rel="noopener noreferrer" className="hover:underline">Read Next.js Docs</a>
      </div>
    </main>
  );
}
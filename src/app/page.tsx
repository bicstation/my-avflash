// src/app/page.tsx (ローカルPCでこの内容で上書き)

// 実行環境（ビルド中か、サーバー稼働中か）に応じてURLを切り替えるための判定
// PM2で起動している場合、Next.jsは API_RUNTIME_URL 環境変数を持つ。
const isServerRunning = process.env.API_RUNTIME_URL !== undefined;

// 使用するAPIのベースURLを決定
const API_BASE_URL = isServerRunning 
  ? process.env.API_RUNTIME_URL // PM2で起動中の場合: NGINX経由 (http://127.0.0.1:3000/api)
  : process.env.API_BUILD_URL; // ビルド中の場合: 外部URLに直接アクセス (https://wp552476.wpx.jp/avflash/api)

// APIから取得するデータの型定義
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
    console.error("APIのURLが設定されていません。next.config.tsを確認してください。");
    return [];
  }

  // 決定したAPI_BASE_URLを使って完全なURLを構築
  const url = `${API_BASE_URL}/works/latest`; 
  
  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
      // cache: 'no-store' を設定することで、毎回最新のデータを取得しようとします
      cache: 'no-store' 
    });

    if (!response.ok) {
      throw new Error(`API fetch failed with status: ${response.status} from ${url}`);
    }

    const data: Work[] = await response.json();
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
                {/* 実際にPHP APIから返されるJSONのキー名を使用 */}
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
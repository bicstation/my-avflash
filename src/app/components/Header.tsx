// src/components/Header.tsx
import Link from 'next/link';

export default function Header() {
  return (
    <header className="bg-gray-800 text-white shadow-lg sticky top-0 z-10">
      <div className="container mx-auto flex items-center justify-between p-3">
        <div className="flex items-center space-x-6">
          <Link href="/" className="text-xl font-bold tracking-wider hover:text-red-400 transition">
            AVFLASH.XYZ
          </Link>
          <div className="hidden sm:flex space-x-4">
            <Link href="/" className="hover:text-red-400 transition border-b-2 border-transparent hover:border-red-400 pb-0.5">
              ホーム
            </Link>
            <Link href="/contents" className="text-red-400 border-b-2 border-red-400 pb-0.5">
              コンテンツ
            </Link>
          </div>
        </div>
        
        {/* ログイン/検索エリア (簡易版) */}
        <div className="flex items-center space-x-3">
          <input 
            type="text" 
            placeholder="bicstation" 
            className="p-1.5 text-sm bg-gray-700 border border-gray-600 rounded w-24 text-white"
          />
          <input 
            type="password" 
            placeholder="••••••••" 
            className="p-1.5 text-sm bg-gray-700 border border-gray-600 rounded w-20 text-white"
          />
          <button className="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 text-sm rounded transition">
            ログイン
          </button>
        </div>
      </div>
    </header>
  );
}
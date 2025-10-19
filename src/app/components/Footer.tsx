// src/components/Footer.tsx
import Link from 'next/link';

export default function Footer() {
  const currentYear = new Date().getFullYear();

  return (
    <footer className="bg-gray-800 text-gray-300 mt-auto">
      <div className="container mx-auto p-8">
        
        {/* 3列フッター */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 border-b border-gray-700 pb-6 mb-6">
          <div>
            <h4 className="text-lg font-semibold text-white mb-3">サービス</h4>
            <ul className="space-y-1 text-sm">
              <li><Link href="/about" className="hover:text-red-400">会社概要</Link></li>
              <li><Link href="/contact" className="hover:text-red-400">お問い合わせ</Link></li>
              <li><Link href="/faq" className="hover:text-red-400">FAQ</Link></li>
            </ul>
          </div>
          <div>
            <h4 className="text-lg font-semibold text-white mb-3">ナビゲーション</h4>
            <ul className="space-y-1 text-sm">
              <li><Link href="/ranking" className="hover:text-red-400">人気ランキング</Link></li>
              <li><Link href="/new" className="hover:text-red-400">最新作品</Link></li>
              <li><Link href="/genres" className="hover:text-red-400">ジャンル一覧</Link></li>
            </ul>
          </div>
          <div>
            <h4 className="text-lg font-semibold text-white mb-3">情報</h4>
            <p className="text-sm">当サイトは、様々なコンテンツ情報を集約し、利便性を高めることを目的としています。</p>
          </div>
        </div>
        
        {/* コピーライト */}
        <div className="text-center text-sm text-gray-500">
          &copy; {currentYear} AVFLASH.XYZ All rights reserved.
        </div>
      </div>
    </footer>
  );
}
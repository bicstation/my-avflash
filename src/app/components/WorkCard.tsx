// src/components/WorkCard.tsx

import Image from 'next/image';
import Link from 'next/link';

interface Work {
  id: number;
  title: string;
  release_date: string;
  cover_url: string;
  price: number;
  product_url: string; 
  brand_name: string; // 仕訳として利用
}

interface WorkCardProps {
  work: Work;
}

export default function WorkCard({ work }: WorkCardProps) {
  // 価格を整形
  const formattedPrice = work.price?.toLocaleString() || '価格不明';

  return (
    <div className="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200 transition-shadow duration-300 transform hover:scale-[1.02]">
      
      {/* 画像エリア (スクショの縦長画像を再現するためアスペクト比を設定) */}
      <div className="relative w-full aspect-[3/4]">
        <Image 
          src={work.cover_url || '/placeholder.jpg'} // 画像URLがない場合はフォールバック
          alt={work.title} 
          fill 
          sizes="(max-width: 640px) 50vw, 25vw"
          style={{ objectFit: 'cover' }}
          className="transition duration-500"
        />
        {/* ID表示 (画像上のオーバーレイ) */}
        <span className="absolute bottom-1 right-1 bg-black/50 text-white text-xs px-1 rounded">ID: {work.id}</span>
      </div>

      {/* コンテンツエリア */}
      <div className="p-3">
        
        {/* 仕訳表示 (ブランド名) */}
        <p className="text-xs text-red-600 font-medium mb-1 truncate">{work.brand_name || '未分類'}</p> 
        
        {/* タイトル */}
        <h3 className="text-sm font-semibold h-9 overflow-hidden mb-1 leading-tight hover:text-blue-600 transition">
          <Link href={`/works/${work.id}`}>{work.title}</Link>
        </h3>

        {/* 価格 */}
        <div className="text-lg font-bold text-gray-900 mb-2">{formattedPrice}</div>

        {/* 公開日 */}
        <p className="text-xs text-gray-500">公開日: {work.release_date}</p>

        {/* アフィリエイトリンクボタン */}
        <a 
          href={work.product_url} 
          target="_blank" 
          rel="noopener noreferrer" 
          className="mt-3 block text-center bg-green-500 text-white text-sm py-1.5 rounded-full hover:bg-green-600 transition"
        >
          アフィリエイトで購入
        </a>
      </div>
    </div>
  );
}
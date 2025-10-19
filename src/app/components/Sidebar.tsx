// src/components/Sidebar.tsx
import Link from 'next/link';
import { Heart, Menu, Star, Film, MonitorPlay, Zap, DollarSign } from 'lucide-react'; // アイコンライブラリの例

export default function Sidebar() {
  return (
    <div className="bg-white p-4 rounded-lg shadow-lg sticky top-20"> {/* Headerの下に固定 */}
      
      {/* メニューヘッダー */}
      <h3 className="flex items-center text-lg font-bold mb-3 border-b pb-2 text-red-600">
        <Heart className="w-5 h-5 mr-2" />
        アイドル委員会
      </h3>

      {/* サイト切り替え */}
      <div className="mb-4 pb-2 border-b border-gray-100">
        <p className="text-sm font-semibold text-gray-600 mb-1">サイト切り替え</p>
        <ul className="space-y-1 text-sm">
          <li><Link href="/site/lemon" className="flex items-center p-1 hover:bg-yellow-50/50 rounded transition"><Zap className="w-4 h-4 text-yellow-500 mr-2" />LEMON (レモン)</Link></li>
          <li><Link href="/site/okashi" className="flex items-center p-1 hover:bg-red-50/50 rounded transition"><DollarSign className="w-4 h-4 text-red-500 mr-2" />OKASHI (お菓子)</Link></li>
          <li><Link href="/site/b10f" className="flex items-center p-1 hover:bg-gray-50/50 rounded transition"><Menu className="w-4 h-4 text-gray-500 mr-2" />B10F (地下1 0階)</Link></li>
        </ul>
      </div>

      {/* ジャンル別ナビ */}
      <div className="mb-4">
        <p className="text-sm font-semibold text-gray-600 mb-2">ジャンル別ナビ</p>
        <ul className="space-y-2 text-sm">
          <li><Link href="/new" className="flex items-center p-1 font-semibold text-red-600 hover:bg-red-50/50 rounded transition"><Zap className="w-4 h-4 mr-2" />最新作品</Link></li>
          <li><Link href="/ranking" className="flex items-center p-1 hover:bg-yellow-50/50 rounded transition"><Star className="w-4 h-4 text-yellow-500 mr-2" />人気ランキング</Link></li>
          <li><Link href="/drama" className="flex items-center p-1 hover:bg-blue-50/50 rounded transition"><Film className="w-4 h-4 text-blue-500 mr-2" />ドラマ・映画</Link></li>
          <li><Link href="/anime" className="flex items-center p-1 hover:bg-green-50/50 rounded transition"><MonitorPlay className="w-4 h-4 text-green-500 mr-2" />アニメ</Link></li>
          <li><Link href="/variety" className="flex items-center p-1 hover:bg-purple-50/50 rounded transition"><Menu className="w-4 h-4 text-purple-500 mr-2" />バラエティ</Link></li>
        </ul>
      </div>
    </div>
  );
}
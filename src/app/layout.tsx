// src/app/layout.tsx (修正案)

import type { Metadata } from "next";
import "./globals.css";
import Header from "@/components/Header";
import Footer from "@/components/Footer";

export const metadata: Metadata = {
  title: "AVFLASH.XYZ - Next.js デモ",
  description: "Next.jsとTailwind CSSによるAPI連携デモサイト",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="ja">
      <body className="bg-gray-100 min-h-screen flex flex-col">
        {/* 画面上部の黒いヘッダー */}
        <Header /> 

        {/* メインコンテンツエリア (Sidebar + Page Content) */}
        <main className="flex-grow container mx-auto p-4 sm:p-6 lg:p-8">
          {children} {/* ここに src/app/page.tsx の内容が入ります */}
        </main>

        {/* 画面下部のフッター */}
        <Footer />
      </body>
    </html>
  );
}
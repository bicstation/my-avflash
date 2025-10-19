// next.config.ts (ローカルPCで修正)

/** @type {import('next').NextConfig} */
const nextConfig = {
  // 外部API URLを環境変数として設定する場合
  env: {
// 1. **外部URL（ビルド時/直接アクセス用）**: ビルド時にデータ取得を試みるためのURL
    //    ビルドサーバー（VPS）から直接アクセス可能なURLを指定
    API_BUILD_URL: 'https://wp552476.wpx.jp/avflash/api', 
    
    // 2. **ローカルプロキシURL（ランタイム時用）**: PM2で動いているサーバーが、
    //    NGINX経由でアクセスするためのローカルURL
    API_RUNTIME_URL: 'http://127.0.0.1:3000/api',
  },
  // VPSでの自己ホスティングに必須
  output: 'standalone', 
};

module.exports = nextConfig;
// next.config.js
/** @type {import('next').NextConfig} */
const nextConfig = {
  // 外部API URLを環境変数として設定する場合
  env: {
    API_BASE_URL: 'https://wp552476.wpx.jp/avflash/api', // ★ ここにPHP APIのURLを設定
  },
  // VPSでの自己ホスティングに必須
  output: 'standalone', 
};
module.exports = nextConfig;
// next.config.ts (ローカルPCでこの内容で上書き)

/** @type {import('next').NextConfig} */
const nextConfig = {
  // VPSでの自己ホスティングに必須
  output: 'standalone', 

  // ★ API_BASE_URLに外部の完全なURLを再設定 ★
  env: {
    API_BASE_URL: 'https://wp552476.wpx.jp/avflash/api', 
  },
};

module.exports = nextConfig;
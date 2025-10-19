// next.config.ts (ローカルPCで修正)

/** @type {import('next').NextConfig} */
const nextConfig = {
  // 外部API URLを環境変数として設定する場合
  env: {
    // ★ 修正後の値: NGINX経由でアクセスするため、Next.jsのローカルアドレスを指定 ★
    API_BASE_URL: 'http://127.0.0.1:3000/api', 
  },
  // VPSでの自己ホスティングに必須
  output: 'standalone', 
};

module.exports = nextConfig;
<footer class="mt-5 pt-3 border-top border-secondary bg-dark">
    <div class="container">
        <div class="row">
            
            <div class="col-md-4 mb-4">
                <h5 class="text-emphasis"><i class="fas fa-video me-2"></i><?php echo config('app.name'); ?></h5>
                <p class="text-muted small">
                    自作MVCフレームワークによるデモサイトです。<br>
                    高速な作品情報提供を目指します。
                </p>
            </div>

            <div class="col-md-4 mb-4">
                <h5 class="text-white"><i class="fas fa-bars me-2"></i>ナビゲーション</h5>
                <ul class="list-unstyled">
                    <li><a href="/" class="text-decoration-none text-muted"><i class="fas fa-home me-2"></i>ホーム</a></li>
                    <li><a href="/sitemap" class="text-decoration-none text-muted"><i class="fas fa-sitemap me-2"></i>サイトマップ</a></li>
                    <li><a href="/contact" class="text-decoration-none text-muted"><i class="fas fa-envelope me-2"></i>お問い合わせ</a></li>
                </ul>
            </div>

            <div class="col-md-4 mb-4">
                <h5 class="text-white"><i class="fas fa-gavel me-2"></i>法的情報</h5>
                <ul class="list-unstyled">
                    <li><a href="/privacy" class="text-decoration-none text-muted"><i class="fas fa-user-secret me-2"></i>プライバシーポリシー</a></li>
                    <li><a href="/terms" class="text-decoration-none text-muted"><i class="fas fa-file-contract me-2"></i>利用規約</a></li>
                    <li><a href="/disclaimer" class="text-decoration-none text-muted"><i class="fas fa-exclamation-triangle me-2"></i>免責事項</a></li>
                </ul>
            </div>
        </div>
        <div class="text-center py-3 border-top border-secondary">
            <p class="mb-0 small text-muted">&copy; 2025 <?php echo config('app.name'); ?>. Powered by <span class="text-emphasis">Custom MVC.</span></p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
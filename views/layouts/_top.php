<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 border-bottom border-danger navbar-compact">
    <div class="container-fluid">
        <a class="navbar-brand text-emphasis" href="<?php echo config('app.base_url'); ?>">
            <i class="fas fa-video me-2"></i> <?php echo config('app.name'); ?>
        </a>
        <div class="d-flex">
            <?php if (isset($is_logged_in) && $is_logged_in): ?>
                <span class="navbar-text me-3 text-white-50">ようこそ、ユーザー名さん</span>
                <a href="/logout" class="btn btn-sm btn-outline-danger"><i class="fas fa-sign-out-alt"></i> ログアウト</a>
            <?php else: ?>
                <form class="d-flex align-items-center login-form-compact" action="/login" method="POST">
                    <input class="form-control form-control-sm me-1" type="email" placeholder="メール" aria-label="Email" name="email">
                    <input class="form-control form-control-sm me-1" type="password" placeholder="パスワード" aria-label="Password" name="password">
                    <button class="btn btn-sm btn-danger" type="submit"><i class="fas fa-lock"></i> ログイン</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</nav>
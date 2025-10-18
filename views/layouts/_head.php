<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($description ?? 'アダルト動画作品の最新情報と作品リスト。自作MVCモデルで構築された高速なサイトです。'); ?>">
    <meta name="keywords" content="アダルト, AV, 最新作品, 動画, <?php echo htmlspecialchars($keywords ?? 'av, works'); ?>">
    <meta name="author" content="<?php echo config('app.name'); ?>">
    
    <title><?php echo htmlspecialchars($title ?? config('app.name')); ?> | <?php echo config('app.name'); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <link rel="stylesheet" href="<?php echo config('app.base_url'); ?>assets/css/styles.css">

</head>
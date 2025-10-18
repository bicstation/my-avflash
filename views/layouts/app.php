<!DOCTYPE html>
<html lang="ja">

<?php 
// HEADを読み込む
require __DIR__ . '/_head.php'; 
?>

<body>

<?php 
// TOPを読み込む
require __DIR__ . '/_top.php'; 
?>

<div class="container">
    
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb bg-dark p-2 rounded small"> 
        <li class="breadcrumb-item"><a href="<?php echo config('app.base_url'); ?>" class="text-emphasis"><i class="fas fa-home"></i> ホーム</a></li>
        <li class="breadcrumb-item active text-white" aria-current="page"><?php echo htmlspecialchars($page_title ?? 'コンテンツ'); ?></li>
      </ol>
    </nav>

    <div class="row">
        
        <div class="col-lg-3 d-none d-lg-block">
            <?php require __DIR__ . '/_sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <main class="main-content-card"> <h2 class="border-bottom border-danger pb-2 mb-4 text-emphasis"><?php echo htmlspecialchars($page_title ?? 'コンテンツ'); ?></h2>
                
                <?php 
                if (isset($content)) {
                    echo $content; 
                }
                ?>
            </main>
        </div>
        
    </div>
</div>

<?php 
// FOOTERを読み込む
require __DIR__ . '/_footer.php'; 
?>

</html>
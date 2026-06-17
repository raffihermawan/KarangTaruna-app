<?php
require_once __DIR__ . '/../helpers/auth-helper.php';
$flash = get_flash();
if ($flash) :
    $type = htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8');
?>
<div class="alert alert-<?php echo $type; ?>">
    <?php echo $message; ?>
</div>
<?php endif; ?>

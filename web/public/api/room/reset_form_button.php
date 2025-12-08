<?php
$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/lang.php';
$roomId = $_GET['id'];
?>
<button hx-get="/api/room/book_form.php?id=<?= $roomId ?>"
        hx-swap="outerHTML"
        class="btn-book">
    <?= $ui['book_btn'] ?>
</button>
<?php
$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/lang.php'; 

$roomId = $_GET['id'] ?? 0;
?>

<form hx-post="/api/room/book.php" hx-swap="outerHTML" class="mt-4 p-4 border border-gold/30 bg-black/20">
    <input type="hidden" name="room_id" value="<?= $roomId ?>">
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
        <div>
            <label class="form-label" style="font-size:0.6rem;">Check-In</label>
            <input type="date" name="check_in" required class="form-input" style="padding: 0.5rem; font-size: 0.8rem;">
        </div>
        <div>
            <label class="form-label" style="font-size:0.6rem;">Check-Out</label>
            <input type="date" name="check_out" required class="form-input" style="padding: 0.5rem; font-size: 0.8rem;">
        </div>
    </div>

    <div class="flex gap-2">
        <button type="submit" class="btn-book" style="font-size: 0.7rem; padding: 0.5rem;">
            CONFIRM
        </button>
        <button type="button" 
                hx-get="/api/room/reset_button.php?id=<?= $roomId ?>" 
                hx-target="closest form" 
                hx-swap="outerHTML"
                class="btn-book" style="border-color: var(--dry-sage); color: var(--dry-sage); font-size: 0.7rem; padding: 0.5rem;">
            CANCEL
        </button>
    </div>
</form>
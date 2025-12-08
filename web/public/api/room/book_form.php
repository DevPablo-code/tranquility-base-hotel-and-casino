<?php
$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/config/lang.php'; 
$roomId = $_GET['id'] ?? 0;
?>

<form hx-post="/api/room/book.php" hx-swap="outerHTML" class="booking-form">
    <input type="hidden" name="room_id" value="<?= $roomId ?>">
    
    <div class="booking-grid">
        <div>
            <label class="booking-label"><?= $ui['lbl_checkin'] ?></label>
            <input type="date" name="check_in" required class="booking-input">
        </div>
        <div>
            <label class="booking-label"><?= $ui['lbl_checkout'] ?></label>
            <input type="date" name="check_out" required class="booking-input">
        </div>
    </div>

    <div class="booking-grid">
        <div>
            <label class="booking-label"><?= $ui['lbl_phone'] ?></label>
            <input type="tel" name="phone" required class="booking-input" placeholder="+380...">
        </div>
        <div>
            <label class="booking-label"><?= $ui['lbl_passport'] ?></label>
            <input type="text" name="passport" required class="booking-input" placeholder="AA123456">
        </div>
    </div>

    <div style="border: 1px solid var(--gold); padding: 10px; margin-bottom: 1rem; background: rgba(212, 175, 55, 0.05);">
        <div style="font-family: var(--font-heading); color: var(--gold); font-size: 0.7rem; margin-bottom: 0.5rem; letter-spacing: 0.1em;">
            <?= $ui['pay_title'] ?>
        </div>
        
        <div style="margin-bottom: 0.5rem;">
            <label class="booking-label"><?= $ui['lbl_card_num'] ?></label>
            <input type="text" name="card_num" required class="booking-input" 
                   placeholder="0000 0000 0000 0000" maxlength="19" 
                   style="letter-spacing: 0.1em; font-family: monospace;">
        </div>
        
        <div class="booking-grid" style="margin-bottom: 0;">
            <div>
                <label class="booking-label"><?= $ui['lbl_card_exp'] ?></label>
                <input type="text" name="card_exp" required class="booking-input" placeholder="MM/YY" maxlength="5">
            </div>
            <div>
                <label class="booking-label"><?= $ui['lbl_card_cvc'] ?></label>
                <input type="password" name="card_cvc" required class="booking-input" placeholder="123" maxlength="3">
            </div>
        </div>
    </div>

    <div class="booking-actions">
        <button type="submit" class="btn-confirm">
            <?= $ui['btn_confirm'] ?>
        </button>
        <button type="button" 
                hx-get="/api/room/reset_form_button.php?id=<?= $roomId ?>" 
                hx-target="closest form" 
                hx-swap="outerHTML"
                class="btn-secondary">
            <?= $ui['btn_cancel'] ?>
        </button>
    </div>
</form>
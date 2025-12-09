<?php
// www/includes/form_template.php

function renderBookingForm(array $ui, int $roomId, array $data = []) {
    
    // Розпаковка даних (щоб при помилці не зникали введені значення)
    $errorMsg = htmlspecialchars($data['error_message'] ?? '');
    
    $firstName = htmlspecialchars($data['first_name'] ?? ''); // <--- НОВЕ
    $lastName = htmlspecialchars($data['last_name'] ?? '');   // <--- НОВЕ
    
    $checkIn = htmlspecialchars($data['check_in'] ?? '');
    $checkOut = htmlspecialchars($data['check_out'] ?? '');
    $phone = htmlspecialchars($data['phone'] ?? '');
    $passportNo = htmlspecialchars($data['passport'] ?? '');
    $cardNum = htmlspecialchars($data['card_num'] ?? '');
    $cardExp = htmlspecialchars($data['card_exp'] ?? '');
    $cardCVC = htmlspecialchars($data['card_cvc'] ?? '');
    
    ob_start(); 
    ?>
    
    <form hx-post="/api/room/book.php" hx-swap="outerHTML" class="booking-form" id="booking-form-<?= $roomId ?>">
        <input type="hidden" name="room_id" value="<?= $roomId ?>">

        <?php if (!empty($errorMsg)): ?>
            <div class="booking-error-message" style="border: 2px solid var(--danger); padding: 10px; margin-bottom: 1rem; color: var(--danger); font-family: var(--font-mono); text-align: center; background: rgba(255, 68, 68, 0.1);">
                <?= $errorMsg ?>
            </div>
        <?php endif; ?>
        
        <div class="booking-grid">
            <div>
                <label class="booking-label"><?= $ui['lbl_checkin'] ?></label>
                <input type="date" name="check_in" value="<?= $checkIn ?>" required class="booking-input">
            </div>
            <div>
                <label class="booking-label"><?= $ui['lbl_checkout'] ?></label>
                <input type="date" name="check_out" value="<?= $checkOut ?>" required class="booking-input">
            </div>
        </div>

        <div class="booking-grid">
            <div>
                <label class="booking-label"><?= $ui['lbl_firstname'] ?? 'First Name' ?></label>
                <input type="text" name="first_name" value="<?= $firstName ?>" required class="booking-input" placeholder="Ivan">
            </div>
            <div>
                <label class="booking-label"><?= $ui['lbl_lastname'] ?? 'Last Name' ?></label>
                <input type="text" name="last_name" value="<?= $lastName ?>" required class="booking-input" placeholder="Petrenko">
            </div>
        </div>

        <div class="booking-grid">
            <div>
                <label class="booking-label"><?= $ui['lbl_phone'] ?></label>
                <input type="tel" name="phone" value="<?= $phone ?>" required class="booking-input" placeholder="+380...">
            </div>
            <div>
                <label class="booking-label"><?= $ui['lbl_passport'] ?></label>
                <input type="text" name="passport" value="<?= $passportNo ?>" required class="booking-input" placeholder="ID/Passport No">
            </div>
        </div>

        <div style="border: 1px solid var(--gold); padding: 10px; margin-bottom: 1rem; background: rgba(212, 175, 55, 0.05);">
            <div style="font-family: var(--font-heading); color: var(--gold); font-size: 0.7rem; margin-bottom: 0.5rem; letter-spacing: 0.1em;">
                <?= $ui['pay_title'] ?>
            </div>
            
            <div style="margin-bottom: 0.5rem;">
                <label class="booking-label"><?= $ui['lbl_card_num'] ?></label>
                <input type="text" name="card_num" value="<?= $cardNum ?>" required class="booking-input" 
                       placeholder="0000 0000 0000 0000" maxlength="19" 
                       style="letter-spacing: 0.1em; font-family: monospace;">
            </div>
            
            <div class="booking-grid" style="margin-bottom: 0;">
                <div>
                    <label class="booking-label"><?= $ui['lbl_card_exp'] ?></label>
                    <input type="text" name="card_exp" value="<?= $cardExp ?>" required class="booking-input" placeholder="MM/YY" maxlength="5">
                </div>
                <div>
                    <label class="booking-label"><?= $ui['lbl_card_cvc'] ?></label>
                    <input type="password" name="card_cvc" value="<?= $cardCVC ?>" required class="booking-input" placeholder="123" maxlength="3">
                </div>
            </div>
        </div>

        <div class="booking-actions">
            <button type="submit" class="btn-confirm">
                <?= $ui['btn_confirm'] ?>
            </button>
            <button type="button" 
                    hx-get="/api/room/reset_form_button.php?id=<?= $roomId ?>" 
                    hx-target="#booking-form-<?= $roomId ?>" 
                    hx-swap="outerHTML"
                    class="btn-secondary">
                <?= $ui['btn_cancel'] ?>
            </button>
        </div>
    </form>
    <?php
    return ob_get_clean();
}
?>
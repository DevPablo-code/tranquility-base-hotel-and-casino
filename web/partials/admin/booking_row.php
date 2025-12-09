<?php
?>
<tr id="booking-row-<?= $b['id'] ?>">
    <td style="color:var(--dry-sage);">#<?= $b['id'] ?></td>
    
    <td>
        <strong style="color:var(--gold); font-size: 0.9rem;">
            <?= htmlspecialchars($b['first_name'] . ' ' . $b['last_name']) ?>
        </strong>
        
        <div style="font-size:0.7rem; color:var(--dry-sage); margin-top: 4px;">
            PH: <?= htmlspecialchars($b['phone']) ?><br>
            ID: <?= htmlspecialchars($b['passport_no'] ?? '-') ?>
        </div>

        <?php if(!empty($b['username'])): ?>
            <div style="font-size:0.6rem; color:var(--light-blue); margin-top:2px;">
                [ACC: <?= htmlspecialchars($b['username']) ?>]
            </div>
        <?php endif; ?>
    </td>

    <td>
        <?= htmlspecialchars($b['room_number']) ?><br>
        <small style="color:var(--dry-sage)"><?= htmlspecialchars($b['room_title'] ?? '') ?></small>
    </td>

    <td style="font-size: 0.75rem;">
        IN: <?= $b['check_in'] ?> <br> 
        OUT: <?= $b['check_out'] ?>
    </td>

    <td style="color:var(--gold); font-weight:bold;">
        <?= (int)$b['total_price'] ?>
    </td>

    <td>
        <?php 
            $statusColor = match($b['status']) {
                'confirmed' => 'var(--light-blue)',
                'cancelled' => 'var(--danger)',
                default => 'var(--soft-peach)'
            };
        ?>
        <span style="color:<?= $statusColor ?>"><?= strtoupper($b['status']) ?></span>
        
        <div style="font-size:0.6rem; margin-top:2px;">
            PAY: 
            <span style="color:<?= $b['payment_status']=='paid' ? 'var(--gold)' : 'var(--danger)' ?>">
                <?= strtoupper($b['payment_status']) ?>
            </span>
        </div>
        <?php if(!empty($b['transaction_id'])): ?>
            <div style="font-size:0.5rem; color:var(--dry-sage); font-family:monospace;">
                <?= substr($b['transaction_id'], 0, 8) ?>...
            </div>
        <?php endif; ?>
    </td>

    <td>
        <button hx-get="/admin/forms/booking_form.php?id=<?= $b['id'] ?>" 
                hx-target="#booking-form-container" 
                hx-swap="innerHTML"
                class="btn-action btn-edit">
            EDIT
        </button>

        <?php if ($b['status'] !== 'cancelled'): ?>
            <button hx-post="/api/admin/change_status.php?id=<?= $b['id'] ?>&status=cancelled"
                    hx-confirm="Cancel booking #<?= $b['id'] ?>?"
                    hx-target="#booking-row-<?= $b['id'] ?>"
                    class="btn-action btn-del">
                CNCL
            </button>
        <?php endif; ?>
    </td>
</tr>
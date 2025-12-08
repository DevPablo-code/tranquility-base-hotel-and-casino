<?php
$statusColor = $s['is_active'] ? 'var(--light-blue)' : 'var(--danger)';
?>
<tr id="staff-row-<?= $s['id'] ?>">
    <td style="color:var(--dry-sage);">#<?= $s['id'] ?></td>
    <td style="color:var(--gold);"><?= htmlspecialchars($s['full_name']) ?></td>
    <td><?= htmlspecialchars($s['position']) ?></td>
    <td><?= number_format($s['salary'], 2) ?></td>
    <td><?= htmlspecialchars($s['employment_date']) ?></td>
    <td style="color:<?= $statusColor ?>;">
        <?= $s['is_active'] ? 'ACTIVE' : 'INACTIVE' ?>
    </td>
    <td>
        <button hx-get="/admin/forms/staff_form.php?id=<?= $s['id'] ?>" 
                hx-target="#form-container" 
                class="btn-action btn-edit">EDIT</button>
        
        <button hx-delete="/api/admin/delete_staff.php?id=<?= $s['id'] ?>" 
                hx-confirm="Delete <?= $s['full_name'] ?>?"
                hx-target="#staff-row-<?= $s['id'] ?>"
                hx-swap="outerHTML"
                class="btn-action btn-del">DEL</button>
    </td>
</tr>
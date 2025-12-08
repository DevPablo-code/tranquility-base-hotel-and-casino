<tr id="room-row-<?= $r['id'] ?>">
    <td style="color:var(--gold); font-weight:bold;"><?= htmlspecialchars($r['number']) ?></td>
    <td>
        <?= htmlspecialchars($r['title'] ?? '---') ?>
        <div style="font-size:0.65rem; color:var(--dry-sage); margin-top:2px;">
            [<?= htmlspecialchars($r['features_list'] ?? '') ?>]
        </div>
    </td>
    <td><?= (int)$r['price'] ?></td>
    <td><?= $r['capacity'] ?></td>
    <td><?= $r['features_list']; ?></td>
    <td style="color: <?= $r['status']=='free'?'var(--light-blue)':'var(--danger)' ?>; font-size:0.7rem;">
        <?= strtoupper($r['status']) ?>
    </td>
    <td>
        <a href="/admin/views/room_details.php?id=<?= $r['id'] ?>" class="btn-action btn-edit" style="margin-right: 10px; text-decoration: none;">
            DETAILS
        </a>

        <button hx-get="/admin/forms/room_form.php?id=<?= $r['id'] ?>" 
                hx-target="#form-container" 
                hx-swap="innerHTML"
                class="btn-action btn-edit">EDIT</button>
        
        <button hx-delete="/api/admin/delete_room.php?id=<?= $r['id'] ?>" 
                hx-confirm="IRREVERSIBLE ACTION: Delete room <?= $r['number'] ?>?"
                hx-target="#room-row-<?= $r['id'] ?>"
                hx-swap="outerHTML"
                class="btn-action btn-del">DEL</button>
    </td>
</tr>
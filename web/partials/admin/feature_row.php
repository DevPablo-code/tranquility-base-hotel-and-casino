<tr id="feature-row-<?= $f['id'] ?>">
    <td style="color:var(--dry-sage);">#<?= $f['id'] ?></td>
    <td style="color:var(--gold); letter-spacing:0.05em;">
        <?= htmlspecialchars($f['name']) ?>
    </td>
    <td>
        <button hx-get="/admin/forms/feature_form.php?id=<?= $f['id'] ?>" 
                hx-target="#form-container" 
                class="btn-action btn-edit">EDIT</button>
        
        <button hx-delete="/api/admin/delete_feature.php?id=<?= $f['id'] ?>" 
                hx-confirm="Delete feature '<?= $f['name'] ?>'?"
                hx-target="#feature-row-<?= $f['id'] ?>"
                hx-swap="outerHTML"
                class="btn-action btn-del">DEL</button>
    </td>
</tr>
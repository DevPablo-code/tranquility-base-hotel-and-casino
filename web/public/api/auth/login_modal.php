<?php
$projectRoot = dirname(__DIR__, 3);

require_once $projectRoot . '/config/lang.php';
?>

<div id="auth-modal" class="modal-backdrop">
    
    <div style="position:absolute; inset:0;" onclick="document.getElementById('auth-modal').remove()"></div>

    <div class="modal-content">
        <button class="modal-close" onclick="document.getElementById('auth-modal').remove()">×</button>
        
        <h2 class="auth-title"><?= $ui['auth_title'] ?></h2>

        <form hx-post="/api/auth/login.php" hx-target="#auth-message">
            <div class="form-group">
                <label class="form-label"><?= $ui['auth_user'] ?></label>
                <input type="text" name="username" required class="form-input" 
                       placeholder="...">
            </div>

            <div class="form-group">
                <label class="form-label"><?= $ui['auth_pass'] ?></label>
                <input type="password" name="password" required class="form-input" 
                       placeholder="••••••••">
            </div>

            <div id="auth-message" style="color: #ff4444; font-family: var(--font-mono); font-size: 0.8rem; text-align: center; margin-bottom: 1rem;"></div>

            <button type="submit" class="btn-auth"><?= $ui['auth_submit'] ?></button>
        </form>

        <div class="auth-footer">
            <?= $ui['auth_new'] ?> 
            <button hx-get="/api/auth/register_modal.php?lang=<?= $lang_code ?>" 
                    hx-target="#auth-modal" 
                    hx-swap="outerHTML"
                    class="link-action">
                <?= $ui['auth_reg'] ?>
            </button>
        </div>
    </div>
</div>
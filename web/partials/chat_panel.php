<div id="intercom-trigger" class="intercom-trigger"
     onclick="document.getElementById('intercom-panel').classList.toggle('hidden')">
    <span>🤖</span>
    <div class="intercom-status"></div>
</div>

<div id="intercom-panel" class="intercom-panel hidden">
    
    <div class="intercom-header">
        <div>
            <h3 class="intercom-title"><?= $ui['chat_title'] ?></h3>
            <div class="intercom-subtitle"><?= $ui['chat_status'] ?></div>
        </div>
        <button onclick="document.getElementById('intercom-panel').classList.add('hidden')" class="intercom-close">✕</button>
    </div>

    <div id="chat-history" class="intercom-history">
        <?php if (empty($_SESSION['chat_history'])): ?>
            <div class="chat-msg mark">
                <span class="mark-label">MARK:</span>
                <?= $ui['chat_greeting'] ?>
            </div>
        <?php else: ?>
            <?php endif; ?>
    </div>

    <div id="ai-loading" class="htmx-indicator">
        > <?= $ui['chat_loading'] ?> <span class="cursor-blink"></span>
    </div>

    <form hx-post="/api/chat/chat.php" 
          hx-target="#chat-history" 
          hx-swap="beforeend"
          hx-indicator="#ai-loading"
          onsubmit="..."
          class="intercom-form">
        
        <input type="text" name="message" required
               class="intercom-input"
               placeholder="<?= $ui['chat_placeholder'] ?>" autocomplete="off">
        
        <button type="submit" class="intercom-send">➤</button>
    </form>
    
    <div class="intercom-footer">
        <button hx-post="/api/chat/reset.php" 
                hx-target="#chat-history" 
                hx-swap="innerHTML" 
                class="btn-purge">
            <?= $ui['chat_purge'] ?>
        </button>
    </div>
</div>
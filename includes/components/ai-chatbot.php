<?php
// ============================================================
// MARVELOUS HOTEL — Interactive AI Chatbot Widget Component
// includes/components/ai-chatbot.php
// ============================================================
$hotelName = getSetting('hotel_name', 'Marvelous Hotel');
$csrfToken = generateCsrfToken();
$activeUserName = $_SESSION['user_name'] ?? '';
$isUserLoggedIn = isLoggedIn();
?>
<!-- AI Chatbot Stylesheet -->
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/ai-chat.css">

<!-- AI Chatbot Floating Widget -->
<div class="ai-fab" id="aiFab">
  <div class="ai-chat-box" id="aiChatBox" role="dialog" aria-label="AI Concierge Chat">
    <!-- Header -->
    <div class="ai-chat-header">
      <div style="display:flex;align-items:center;gap:10px;">
        <div class="ai-chat-avatar">
          <i class="fa-solid fa-robot"></i>
        </div>
        <div>
          <div class="ai-chat-name">
            Marvi <span class="ai-chat-badge">AI Assistant</span>
          </div>
          <div class="ai-chat-status">
            <i class="fa-solid fa-circle" style="font-size:0.5rem;"></i> Online &amp; Ready to Help
          </div>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:6px;">
        <button class="ai-chat-btn-action" onclick="resetAIChat()" title="Clear chat history / Restart" aria-label="Restart chat">
          <i class="fa-solid fa-rotate-right"></i>
        </button>
        <button class="ai-chat-close" onclick="closeAIChat()" aria-label="Close chat">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <!-- Messages Area -->
    <div class="ai-chat-messages" id="aiMessages">
      <div class="ai-msg bot">
        <div class="ai-msg-icon"><i class="fa-solid fa-robot"></i></div>
        <div class="ai-msg-bubble">
          <?php if ($isUserLoggedIn && !empty($activeUserName)): ?>
            Kumusta, <strong><?= h($activeUserName) ?></strong>! 👋 Ako si <strong>Marvi</strong>, your personal AI concierge at <?= h($hotelName) ?>.<br><br>
            Handa akong mag-rekomenda ng kwarto, mag-check ng inyong booking status, o sumagot sa inyong mga tanong!
          <?php else: ?>
            Kumusta! Hello! 👋 Ako si <strong>Marvi</strong>, your AI concierge at <?= h($hotelName) ?>.<br><br>
            You can ask me in <strong>English</strong> or <strong>Tagalog</strong>. Awtomatiko kong sasagutin ang tanong mo sa parehong wika!
          <?php endif; ?>
          
          <div class="ai-chips-container">
            <div class="ai-chips-label">Quick Inquiries / Mabilisang Tanong:</div>
            <div class="ai-chips-wrap">
              <?php if ($isUserLoggedIn): ?>
                <button type="button" class="ai-chip" onclick="sendQuickPrompt('Kamusta ang booking ko?')">
                  <i class="fa-solid fa-receipt" style="color:var(--gold);"></i> 🇵🇭 Kamusta ang booking ko?
                </button>
              <?php endif; ?>
              <button type="button" class="ai-chip" onclick="sendQuickPrompt('1k ano maganda room')">
                <i class="fa-solid fa-coins" style="color:var(--gold);"></i> 🇵🇭 1k Badyet: Anong magandang room?
              </button>
              <button type="button" class="ai-chip" onclick="sendQuickPrompt('May available bang kwarto para sa mag-asawa o 2 tao?')">
                <i class="fa-solid fa-user-group" style="color:#60a5fa;"></i> 🇵🇭 Pang 2 Tao / Mag-asawa
              </button>
              <button type="button" class="ai-chip" onclick="sendQuickPrompt('Magkano ang pinakamurang dormitory bed?')">
                <i class="fa-solid fa-bed" style="color:#4ade80;"></i> 🇵🇭 Murang Dormitory Bed
              </button>
              <button type="button" class="ai-chip" onclick="sendQuickPrompt('Saan ang location ninyo at paano pumunta?')">
                <i class="fa-solid fa-location-dot" style="color:#f87171;"></i> 🇵🇭 Location &amp; Directions
              </button>
              <button type="button" class="ai-chip" onclick="sendQuickPrompt('Paano magbayad gamit ang GCash o Maya?')">
                <i class="fa-solid fa-credit-card" style="color:#38bdf8;"></i> 🇵🇭 Paano magbayad?
              </button>
              <button type="button" class="ai-chip" onclick="sendQuickPrompt('What are the check-in and check-out times?')">
                <i class="fa-solid fa-clock"></i> 🇬🇧 Check-in/out times
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Input Bar -->
    <div class="ai-chat-input-wrap">
      <input 
        type="text" 
        class="ai-chat-input" 
        id="aiInput" 
        placeholder="Ask Marvi in English o Tagalog..." 
        autocomplete="off"
        onkeydown="if(event.key==='Enter'){ event.preventDefault(); sendAIMessage(); }"
      >
      <button type="button" class="ai-chat-send" onclick="sendAIMessage()" aria-label="Send message">
        <i class="fa-solid fa-paper-plane"></i>
      </button>
    </div>
  </div>

  <!-- Floating Trigger Button -->
  <button type="button" class="ai-fab-btn" onclick="toggleAIChat()" id="aiFabBtn" aria-label="Toggle AI Chat">
    <i class="fa-solid fa-robot" id="aiFabIcon"></i>
    <div class="ai-pulse"></div>
  </button>
</div>

<!-- AI Chatbot Client Script -->
<script src="<?= ASSETS_URL ?>/js/ai-chat.js"></script>

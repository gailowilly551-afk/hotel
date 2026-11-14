/* ============================================================
   MARVELOUS HOTEL — Interactive AI Chatbot Frontend
   assets/js/ai-chat.js
   ============================================================ */

/**
 * Resolves the dynamic backend endpoint for the AI Chatbot
 */
function getChatbotEndpoint() {
  if (window.HOTEL_AI_ENDPOINT) {
    return window.HOTEL_AI_ENDPOINT;
  }
  const isHotelSubdir = window.location.pathname.startsWith('/hotel');
  return (isHotelSubdir ? '/hotel' : '') + '/ai/chatbot.php';
}

/**
 * Appends a message bubble to the chat container
 */
function appendAIMessage(htmlText, isUser = false, isWarning = false) {
  const container = document.getElementById('aiMessages');
  if (!container) return;

  const msgDiv = document.createElement('div');
  msgDiv.className = `ai-msg ${isUser ? 'user' : 'bot'}`;

  const iconDiv = document.createElement('div');
  iconDiv.className = 'ai-msg-icon';
  iconDiv.innerHTML = isUser
    ? '<i class="fa-solid fa-user"></i>'
    : '<i class="fa-solid fa-robot"></i>';

  const bubbleDiv = document.createElement('div');
  bubbleDiv.className = 'ai-msg-bubble';

  if (isWarning) {
    bubbleDiv.innerHTML = `
      <div class="ai-warning-box">
        <i class="fa-solid fa-triangle-exclamation" style="font-size:1.1rem;margin-top:2px;"></i>
        <div>${htmlText}</div>
      </div>
    `;
  } else {
    bubbleDiv.innerHTML = htmlText;
  }

  msgDiv.appendChild(iconDiv);
  msgDiv.appendChild(bubbleDiv);
  container.appendChild(msgDiv);
  container.scrollTop = container.scrollHeight;
}

/**
 * Shows three-dot typing indicator
 */
function showAITyping() {
  const container = document.getElementById('aiMessages');
  if (!container || document.getElementById('aiTypingIndicator')) return;

  const typingDiv = document.createElement('div');
  typingDiv.className = 'ai-msg bot';
  typingDiv.id = 'aiTypingIndicator';
  typingDiv.innerHTML = `
    <div class="ai-msg-icon"><i class="fa-solid fa-robot"></i></div>
    <div class="ai-typing"><span></span><span></span><span></span></div>
  `;
  container.appendChild(typingDiv);
  container.scrollTop = container.scrollHeight;
}

/**
 * Removes typing indicator
 */
function removeAITyping() {
  const indicator = document.getElementById('aiTypingIndicator');
  if (indicator) indicator.remove();
}

/**
 * Sends a quick preset prompt (suggestion chip)
 */
function sendQuickPrompt(promptText) {
  const input = document.getElementById('aiInput');
  if (!input) return;
  input.value = promptText;
  sendAIMessage();
}

/**
 * Primary message sending routine
 */
async function sendAIMessage() {
  const input = document.getElementById('aiInput');
  const sendBtn = document.querySelector('.ai-chat-send');
  if (!input) return;

  const message = input.value.trim();
  if (!message) return;

  // Add user bubble
  appendAIMessage(escapeHtml(message), true);
  input.value = '';
  input.disabled = true;
  if (sendBtn) sendBtn.disabled = true;

  showAITyping();

  try {
    const endpoint = getChatbotEndpoint();
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const res = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify({
        message: message,
        csrf_token: csrfToken
      })
    });

    if (!res.ok) {
      throw new Error(`Server responded with HTTP ${res.status}`);
    }

    const data = await res.json();
    removeAITyping();

    if (data.status === 'warning' || data.is_alert) {
      appendAIMessage(data.response, false, true);
    } else if (data.response) {
      appendAIMessage(data.response, false, false);
    } else {
      appendAIMessage('Paumanhin, walang naibalik na tugon. Maaari mong subukan muli.', false);
    }
  } catch (err) {
    removeAITyping();
    console.error('AI Chatbot fetch error:', err);
    appendAIMessage(
      'Paumanhin, nagkaroon ng sandaling aberya sa koneksyon ng aming AI assistant. Pakisubukang magtanong muli o mag-refresh.',
      false
    );
  } finally {
    input.disabled = false;
    if (sendBtn) sendBtn.disabled = false;
    input.focus();
  }
}

/**
 * Simple HTML escape helper for user input
 */
function escapeHtml(text) {
  const div = document.createElement('div');
  div.innerText = text;
  return div.innerHTML;
}

/**
 * Modal / Drawer open/close/toggle controls
 */
function openAIChat() {
  const box = document.getElementById('aiChatBox');
  const icon = document.getElementById('aiFabIcon');
  if (box) {
    box.classList.add('open');
    const input = document.getElementById('aiInput');
    if (input) setTimeout(() => input.focus(), 250);
  }
  if (icon) {
    icon.className = 'fa-solid fa-xmark';
  }
}

function closeAIChat() {
  const box = document.getElementById('aiChatBox');
  const icon = document.getElementById('aiFabIcon');
  if (box) box.classList.remove('open');
  if (icon) icon.className = 'fa-solid fa-robot';
}

function toggleAIChat() {
  const box = document.getElementById('aiChatBox');
  if (box && box.classList.contains('open')) {
    closeAIChat();
  } else {
    openAIChat();
  }
}

/**
 * Resets / Clears the Chatbot History back to initial greeting
 */
function resetAIChat() {
  const container = document.getElementById('aiMessages');
  if (!container) return;
  const firstMsg = container.querySelector('.ai-msg.bot');
  if (firstMsg) {
    const clone = firstMsg.cloneNode(true);
    container.innerHTML = '';
    container.appendChild(clone);
  }
  const input = document.getElementById('aiInput');
  if (input) input.value = '';
}

// Auto-bind Escape key to close chat
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    const box = document.getElementById('aiChatBox');
    if (box && box.classList.contains('open')) {
      closeAIChat();
    }
  }
});

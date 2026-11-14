/* ============================================================
   MARVELOUS HOTEL — Global Frontend Script
   assets/js/main.js
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
  // Auto-dismiss flash alerts after 5 seconds
  const flash = document.getElementById('flashMsg');
  if (flash) {
    setTimeout(() => {
      flash.style.opacity = '0';
      flash.style.transform = 'translateY(-10px)';
      flash.style.transition = 'all 0.5s ease';
      setTimeout(() => flash.remove(), 500);
    }, 5000);
  }
});

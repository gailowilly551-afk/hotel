/* ============================================================
   MARVELOUS HOTEL — Global Core JS Utilities
   assets/js/core.js
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Auto dismiss flash notifications
  const flashes = document.querySelectorAll('.flash-message');
  flashes.forEach(flash => {
    setTimeout(() => {
      flash.style.opacity = '0';
      flash.style.transform = 'translateY(-10px)';
      flash.style.transition = 'all 0.4s ease';
      setTimeout(() => flash.remove(), 400);
    }, 4500);
  });

  // 2. Global Mobile Sidebar Drawer Toggle (Works for Admin, Staff, & User)
  const toggleBtns = document.querySelectorAll('[data-sidebar-toggle]');
  
  // Ensure a reusable mobile overlay exists
  let sidebarOverlay = document.getElementById('globalSidebarOverlay');
  if (!sidebarOverlay) {
    sidebarOverlay = document.createElement('div');
    sidebarOverlay.id = 'globalSidebarOverlay';
    sidebarOverlay.style.cssText = 'display:none;position:fixed;inset:0;background:rgba(0,0,0,0.65);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);z-index:999;transition:opacity 0.25s ease;';
    document.body.appendChild(sidebarOverlay);
  }

  function setSidebarState(open) {
    const sidebar = document.querySelector('.admin-sidebar, .staff-sidebar, .user-sidebar, .sidebar');
    if (!sidebar) return;
    if (open) {
      sidebar.classList.add('open');
      sidebarOverlay.style.display = 'block';
      document.body.style.overflow = 'hidden'; // lock scroll on phones
    } else {
      sidebar.classList.remove('open');
      sidebarOverlay.style.display = 'none';
      document.body.style.overflow = '';
    }
  }

  toggleBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const sidebar = document.querySelector('.admin-sidebar, .staff-sidebar, .user-sidebar, .sidebar');
      const willOpen = !sidebar?.classList.contains('open');
      setSidebarState(willOpen);
    });
  });

  sidebarOverlay.addEventListener('click', () => setSidebarState(false));

  // Close sidebar on Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') setSidebarState(false);
  });
});

// Helper for modal controls
function openModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.add('open');
}
function closeModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.remove('open');
}

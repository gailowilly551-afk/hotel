/**
 * Marvelous Hotel - Universal Website Share & QR Code Helper
 * Enables native mobile share (Messenger, Viber, WhatsApp, etc.), 
 * one-click copy to clipboard, and instant QR Code display.
 */
(function() {
  function getShareData() {
    var shareUrl = window.location.href.split('#')[0];
    var title = document.title || 'Marvelous Hotel & Dormitory';
    var text = 'Check out Marvelous Hotel & Dormitory! Book rooms, dormitories, and studios with AI-powered recommendations.';
    return { title: title, text: text, url: shareUrl };
  }

  function showToast(msg) {
    var existing = document.getElementById('mh-share-toast');
    if (existing) existing.remove();

    var toast = document.createElement('div');
    toast.id = 'mh-share-toast';
    toast.innerHTML = '<i class="fa-solid fa-circle-check" style="color:var(--gold,#c9a84c);"></i> ' + msg;
    toast.style.cssText = 'position:fixed;bottom:30px;left:50%;transform:translateX(-50%);background:rgba(10,15,44,0.95);color:#fff;border:1px solid #c9a84c;padding:12px 24px;border-radius:30px;font-size:14px;font-weight:600;z-index:99999;box-shadow:0 10px 30px rgba(0,0,0,0.5);display:flex;align-items:center;gap:10px;animation:mhFadeUp 0.3s ease;backdrop-filter:blur(8px);';
    document.body.appendChild(toast);

    setTimeout(function() {
      if (toast) {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.4s ease';
        setTimeout(function() { toast.remove(); }, 400);
      }
    }, 2500);
  }

  function openShareModal() {
    var existing = document.getElementById('mh-share-modal');
    if (existing) {
      existing.style.display = 'flex';
      return;
    }

    var data = getShareData();
    var qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + encodeURIComponent(data.url) + '&margin=10';

    var modal = document.createElement('div');
    modal.id = 'mh-share-modal';
    modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.75);backdrop-filter:blur(6px);z-index:99998;display:flex;align-items:center;justify-content:center;padding:20px;';
    modal.innerHTML = 
      '<div style="background:#0e1630;border:1px solid rgba(201,168,76,0.3);border-radius:18px;max-width:440px;width:100%;padding:28px;position:relative;box-shadow:0 20px 60px rgba(0,0,0,0.6);text-align:center;font-family:inherit;color:#fff;">' +
        '<button id="mh-share-close" style="position:absolute;top:16px;right:16px;background:none;border:none;color:#94a3b8;font-size:20px;cursor:pointer;padding:4px 8px;">&times;</button>' +
        '<div style="width:48px;height:48px;border-radius:50%;background:rgba(201,168,76,0.15);border:1px solid #c9a84c;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;color:#c9a84c;font-size:20px;">' +
          '<i class="fa-solid fa-share-nodes"></i>' +
        '</div>' +
        '<h3 style="margin:0 0 6px;font-size:1.25rem;font-weight:700;color:#fff;">Share Marvelous Hotel</h3>' +
        '<p style="margin:0 0 20px;font-size:0.875rem;color:#94a3b8;line-height:1.5;">I-share ang link sa iyong mga kaibigan, pamilya, o panelists gamit ang QR code o copy link.</p>' +
        '<div style="background:#fff;padding:12px;border-radius:12px;display:inline-block;margin-bottom:20px;box-shadow:0 4px 15px rgba(0,0,0,0.3);">' +
          '<img src="' + qrApiUrl + '" alt="QR Code" width="180" height="180" style="display:block;border-radius:6px;">' +
        '</div>' +
        '<div style="display:flex;gap:8px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);padding:6px 6px 6px 14px;border-radius:10px;align-items:center;margin-bottom:18px;">' +
          '<input type="text" id="mh-share-link-input" readonly value="' + data.url + '" style="background:none;border:none;color:#e2e8f0;font-size:0.85rem;width:100%;outline:none;">' +
          '<button id="mh-share-copy-btn" style="background:#c9a84c;color:#0a0f2c;border:none;padding:8px 16px;border-radius:8px;font-weight:700;font-size:0.85rem;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:6px;">' +
            '<i class="fa-solid fa-copy"></i> Copy' +
          '</button>' +
        '</div>' +
        '<div style="display:flex;gap:10px;justify-content:center;">' +
          '<a href="https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(data.url) + '" target="_blank" rel="noopener" style="flex:1;background:rgba(24,119,242,0.15);color:#4e9af1;border:1px solid rgba(24,119,242,0.3);padding:10px;border-radius:8px;text-decoration:none;font-size:0.85rem;font-weight:600;display:flex;align-items:center;justify-content:center;gap:6px;">' +
            '<i class="fa-brands fa-facebook"></i> Facebook' +
          '</a>' +
          '<a href="https://twitter.com/intent/tweet?url=' + encodeURIComponent(data.url) + '&text=' + encodeURIComponent(data.title) + '" target="_blank" rel="noopener" style="flex:1;background:rgba(255,255,255,0.08);color:#fff;border:1px solid rgba(255,255,255,0.2);padding:10px;border-radius:8px;text-decoration:none;font-size:0.85rem;font-weight:600;display:flex;align-items:center;justify-content:center;gap:6px;">' +
            '<i class="fa-brands fa-x-twitter"></i> Twitter/X' +
          '</a>' +
        '</div>' +
      '</div>';

    document.body.appendChild(modal);

    document.getElementById('mh-share-close').onclick = function() {
      modal.style.display = 'none';
    };

    modal.onclick = function(e) {
      if (e.target === modal) modal.style.display = 'none';
    };

    document.getElementById('mh-share-copy-btn').onclick = function() {
      var copyInput = document.getElementById('mh-share-link-input');
      copyInput.select();
      copyInput.setSelectionRange(0, 99999);
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(copyInput.value).then(function() {
          showToast('Link copied to clipboard!');
        });
      } else {
        document.execCommand('copy');
        showToast('Link copied to clipboard!');
      }
    };
  }

  window.triggerShareWebsite = function() {
    var data = getShareData();
    if (navigator.share) {
      navigator.share({
        title: data.title,
        text: data.text,
        url: data.url
      }).catch(function(err) {
        if (err.name !== 'AbortError') {
          openShareModal();
        }
      });
    } else {
      openShareModal();
    }
  };

  // Wire any element with data-action="share-website"
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-action="share-website"]').forEach(function(el) {
      el.addEventListener('click', function(e) {
        e.preventDefault();
        window.triggerShareWebsite();
      });
    });
  });
})();


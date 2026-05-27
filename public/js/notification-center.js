(function () {
  'use strict';

  const summaryUrl = '/notifications/summary';
  const markAllReadUrl = '/notifications/read-all';
  const pollIntervalMs = 15000;
  const seenKey = 'seen_notification_ids';

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const badgeEl = document.getElementById('notificationCountBadge');
  const listEl = document.getElementById('notificationList');
  const summaryEl = document.getElementById('notificationSummaryText');
  const markAllButton = document.getElementById('markAllNotificationsReadButton');

  if (!badgeEl || !listEl || !summaryEl || !markAllButton) {
    return;
  }

  const seenIds = new Set(JSON.parse(sessionStorage.getItem(seenKey) || '[]'));

  function saveSeen() {
    sessionStorage.setItem(seenKey, JSON.stringify(Array.from(seenIds).slice(-200)));
  }

  function requestBrowserPermission() {
    if (!('Notification' in window)) {
      return;
    }
    if (Notification.permission === 'default') {
      Notification.requestPermission().catch(() => {});
    }
  }

  function showBrowserPush(notification) {
    if (!('Notification' in window) || Notification.permission !== 'granted') {
      return;
    }

    const browserNotice = new Notification(notification.title, {
      body: notification.message,
      tag: `user-notification-${notification.id}`,
    });

    browserNotice.onclick = function () {
      window.focus();
      markNotificationRead(notification.id);
    };
  }

  function renderSummary(unreadCount) {
    badgeEl.textContent = String(unreadCount);
    badgeEl.style.display = unreadCount > 0 ? 'inline-block' : 'none';
    summaryEl.textContent = `You have ${unreadCount} new notifications`;
  }

  function renderNotifications(items) {
    if (!Array.isArray(items) || items.length === 0) {
      listEl.innerHTML = '<div class="dropdown-item py-3 text-muted">No notifications yet.</div>';
      return;
    }

    const html = items
      .map((item) => {
        const unreadClass = item.is_read ? '' : 'fw-bold';
        const safeTitle = (item.title || 'Notification').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const safeMessage = (item.message || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const safeTime = (item.created_at_human || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return `
          <button type="button" class="dropdown-item preview-item py-2 notification-item ${unreadClass}" data-id="${item.id}">
            <div class="preview-item-content flex-grow-1">
              <h6 class="preview-subject mb-1">${safeTitle}</h6>
              <p class="text-muted mb-1 small">${safeMessage}</p>
              <small class="text-muted">${safeTime}</small>
            </div>
          </button>
        `;
      })
      .join('');

    listEl.innerHTML = html;
  }

  function attachNotificationClickHandlers() {
    listEl.querySelectorAll('.notification-item').forEach((button) => {
      button.addEventListener('click', function () {
        const id = this.getAttribute('data-id');
        if (id) {
          markNotificationRead(id);
        }
      });
    });
  }

  async function markNotificationRead(id) {
    await fetch(`/notifications/${id}/read`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        Accept: 'application/json',
      },
      body: JSON.stringify({}),
    });
    fetchAndRender();
  }

  async function markAllRead() {
    await fetch(markAllReadUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        Accept: 'application/json',
      },
      body: JSON.stringify({}),
    });
    fetchAndRender();
  }

  async function fetchAndRender() {
    try {
      const response = await fetch(summaryUrl, {
        headers: {
          Accept: 'application/json',
        },
      });

      if (!response.ok) {
        return;
      }

      const data = await response.json();
      const unreadCount = data.unread_count || 0;
      const notifications = data.notifications || [];

      renderSummary(unreadCount);
      renderNotifications(notifications);
      attachNotificationClickHandlers();

      notifications.forEach((item) => {
        if (seenIds.has(item.id)) {
          return;
        }
        seenIds.add(item.id);
        if (!item.is_read) {
          showBrowserPush(item);
        }
      });
      saveSeen();
    } catch (error) {
      console.error('Failed to fetch notifications', error);
    }
  }

  markAllButton.addEventListener('click', function (event) {
    event.preventDefault();
    markAllRead();
  });

  requestBrowserPermission();
  fetchAndRender();
  window.setInterval(fetchAndRender, pollIntervalMs);
})();

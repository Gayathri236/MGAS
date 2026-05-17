const App = {
  state: {
    admin: null,
    toastContainer: null,
  },

  init() {
    this.state.toastContainer = document.getElementById('toastContainer');
    this.initActiveNav();
  },

  initActiveNav() {
    const currentPage = window.location.pathname.split('/').pop() || 'dashboard.html';
    document.querySelectorAll('.nav-item').forEach(item => {
      item.classList.remove('active');
      const href = item.getAttribute('href');
      if (href === currentPage) {
        item.classList.add('active');
      }
    });
  },

  async checkAuth() {
    try {
      const res = await fetch('api/auth.php?action=check');
      const data = await res.json();
      if (!data.logged_in) {
        window.location.href = 'login.html';
        return false;
      }
      if (data.admin) {
        this.state.admin = data.admin;
        const avatar = document.getElementById('userAvatar');
        const name = document.getElementById('userName');
        const role = document.getElementById('userRole');
        if (avatar) avatar.textContent = data.admin.name.charAt(0).toUpperCase();
        if (name) name.textContent = data.admin.name;
        if (role) role.textContent = App.capitalize(data.admin.role);
      }
      return true;
    } catch {
      return false;
    }
  },

  async logout() {
    try {
      await fetch('api/auth.php?action=logout', { method: 'POST' });
    } catch {}
    window.location.href = 'login.html';
  },

  async api(endpoint, options = {}) {
    const config = {
      headers: { 'Content-Type': 'application/json' },
      ...options,
    };
    if (options.body && !(options.body instanceof FormData)) {
      config.body = JSON.stringify(options.body);
    }
    if (options.body instanceof FormData) {
      delete config.headers['Content-Type'];
    }
    try {
      const res = await fetch(endpoint, config);
      const data = await res.json();
      if (!res.ok && data.error) {
        App.showToast('error', 'Error', data.error);
        return null;
      }
      return data;
    } catch (err) {
      App.showToast('error', 'Connection Error', 'Unable to reach server');
      return null;
    }
  },

  showToast(type, title, message = '') {
    if (!this.state.toastContainer) return;
    const icons = {
      success: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
      error: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
      warning: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
      info: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>',
    };
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<span class="toast-icon">${icons[type]}</span><div class="toast-content"><div class="toast-title">${title}</div>${message ? `<div class="toast-message">${message}</div>` : ''}</div>`;
    this.state.toastContainer.appendChild(toast);
    setTimeout(() => {
      toast.style.animation = 'toastOut 0.3s ease forwards';
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  },

  formatCurrency(amount) {
    return 'Rs ' + parseFloat(amount || 0).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
  },

  formatDate(date) {
    if (!date) return '-';
    const d = new Date(date);
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  },

  formatDateTime(datetime) {
    if (!datetime) return '-';
    const d = new Date(datetime);
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
  },

  capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1).replace(/_/g, ' ');
  },

  getBadgeClass(status) {
    const map = {
      pending: 'warning', processing: 'info', shipped: 'info',
      delivered: 'success', cancelled: 'danger', paid: 'success',
      failed: 'danger', refunded: 'warning', blocked: 'danger',
      active: 'success', scheduled: 'info', in_transit: 'info',
      out: 'danger', low: 'warning', good: 'success',
    };
    return map[status] || 'default';
  },

  debounce(fn, ms = 300) {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), ms);
    };
  },

  truncate(str, len = 50) {
    if (!str) return '';
    return str.length > len ? str.substring(0, len) + '...' : str;
  },

  showSkeleton(container, count = 3, type = 'card') {
    if (typeof container === 'string') container = document.getElementById(container);
    if (!container) return;
    let html = '';
    for (let i = 0; i < count; i++) {
      if (type === 'card') {
        html += `<div class="skeleton-card" style="margin-bottom:12px;"><div class="skeleton skeleton-line w60"></div><div class="skeleton skeleton-line w80"></div><div class="skeleton skeleton-line w40"></div></div>`;
      } else if (type === 'table') {
        html += `<div style="display:flex;gap:12px;padding:12px 0;border-bottom:1px solid var(--border);"><div class="skeleton skeleton-avatar"></div><div style="flex:1;"><div class="skeleton skeleton-line w60"></div><div class="skeleton skeleton-line w40"></div></div><div class="skeleton skeleton-line" style="width:80px;"></div></div>`;
      } else if (type === 'stat') {
        html += `<div class="skeleton-card"><div class="skeleton skeleton-line w40"></div><div class="skeleton skeleton-line" style="height:28px;width:60%;margin-top:8px;"></div></div>`;
      }
    }
    container.innerHTML = html;
  },

  setupMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobileOverlay');
    const toggle = document.getElementById('menuToggle');
    if (!sidebar || !overlay || !toggle) return;
    toggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      overlay.classList.toggle('active');
    });
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('active');
    });
  },

  openModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('active');
  },

  closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('active');
  },
};

document.addEventListener('DOMContentLoaded', () => App.init());

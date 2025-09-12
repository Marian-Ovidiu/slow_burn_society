  // helper globale: toast('messaggio', 'success'|'error'|'info')
  window.toast = (msg, type = 'info', opts = {}) => {
    const bg = {
      success: 'linear-gradient(to right, #22c55e, #16a34a)',
      error:   'linear-gradient(to right, #ef4444, #dc2626)',
      info:    'linear-gradient(to right, #3b82f6, #2563eb)',
    }[type] || 'linear-gradient(to right, #111827, #374151)';

    Toastify({
      text: msg,
      duration: 3000,
      close: true,
      gravity: 'bottom',
      position: 'right',
      stopOnFocus: true,
      style: { background: bg },
      ...opts
    }).showToast();
  };

import './bootstrap';
import Alpine from 'alpinejs';
import 'flatpickr/dist/flatpickr.min.css';
import flatpickr from 'flatpickr';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
  // Flatpickr
  document.querySelectorAll('.flatpickr-range').forEach((el) => {
    flatpickr(el, { mode: 'range', dateFormat: 'Y-m-d' });
  });
  document.querySelectorAll('.flatpickr').forEach((el) => {
    flatpickr(el, { dateFormat: 'Y-m-d' });
  });

  // Toasts auto hide
  document.querySelectorAll('[data-toast]')?.forEach((toast) => {
    const timeout = parseInt(toast.dataset.timeout || '4000', 10);
    const hide = () => {
      toast.classList.remove('animate-toast-in');
      toast.classList.add('animate-toast-out');
      setTimeout(() => toast.remove(), 180);
    };
    const btn = toast.querySelector('[data-toast-close]');
    if (btn) btn.addEventListener('click', hide);
    if (timeout > 0) setTimeout(hide, timeout);
  });

  // Sidebar groups toggle + persist
  document.querySelectorAll('[data-group-toggle]')?.forEach((btn) => {
    const targetId = btn.getAttribute('data-group-toggle');
    const target = document.getElementById(targetId);
    const chevron = btn.querySelector('[data-chevron]');
    const key = 'siptuv3:group:' + targetId;
    const setState = (open) => {
      if (!target) return;
      target.style.display = open ? '' : 'none';
      btn.setAttribute('aria-expanded', String(open));
      localStorage.setItem(key, open ? '1' : '0');
      if (chevron) chevron.classList.toggle('rotate-180', open);
    };
    const saved = localStorage.getItem(key);
    if (saved !== null) setState(saved === '1');
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const open = btn.getAttribute('aria-expanded') !== 'true';
      setState(open);
    });
  });
});

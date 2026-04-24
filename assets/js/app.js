// assets/js/app.js
"use strict";
import '../css/app.css';

// (Optional: your jQuery/WP helpers here; DO NOT import or start Alpine here.)
document.addEventListener('DOMContentLoaded', () => {
  const checkbox = document.querySelector('#ship-to-different-address-checkbox');
  const shipping = document.querySelector('.shipping_address');
  if (checkbox && shipping) {
    const sync = () => { shipping.style.display = checkbox.checked ? 'block' : 'none'; };
    checkbox.addEventListener('change', sync);
    sync();
  }
});


(function () {
  function enhanceNotice(el, type) {
    // Set the appropriate live region + role so SRs announce changes
    if (type === 'error') {
      el.setAttribute('role', 'alert');
      el.setAttribute('aria-live', 'assertive');
    } else {
      el.setAttribute('role', 'status');
      el.setAttribute('aria-live', 'polite');
    }
  }

  function applyToExisting() {
    document.querySelectorAll('.woocommerce-error').forEach(function (ul) {
      enhanceNotice(ul, 'error');
      ul.querySelectorAll('li').forEach(function (li) { li.setAttribute('role', 'alert'); });
    });
    document.querySelectorAll('.woocommerce-message').forEach(function (n) {
      enhanceNotice(n, 'message');
    });
    document.querySelectorAll('.woocommerce-info').forEach(function (n) {
      // Woo sometimes outputs <ul class="woocommerce-info"> or a single <div>
      enhanceNotice(n, 'info');
    });
  }

  // Initial pass
  applyToExisting();

  // Watch for notices added dynamically (AJAX add-to-cart, etc.)
  var wrapper = document.querySelector('.woocommerce-notices-wrapper') || document.body;
  if (!wrapper) return;
  try {
    new MutationObserver(function (muts) {
      muts.forEach(function (m) {
        m.addedNodes && m.addedNodes.forEach(function (node) {
          if (!(node instanceof Element)) return;
          if (node.classList && (node.classList.contains('woocommerce-message') ||
            node.classList.contains('woocommerce-info') ||
            node.classList.contains('woocommerce-error'))) {
            applyToExisting();
          } else {
            // Also check descendants (some plugins insert wrappers)
            if (node.querySelector) {
              if (node.querySelector('.woocommerce-message, .woocommerce-info, .woocommerce-error')) {
                applyToExisting();
              }
            }
          }
        });
      });
    }).observe(wrapper, { childList: true, subtree: true });
  } catch (e) { }
})();

// Flex counters (counters_001): must live here — WP Rocket Delay JS defers inline <script> in HTML,
// so x-data="countersSection()" would run before the global function exists.
document.addEventListener('alpine:init', () => {
  const A = window.Alpine;
  if (!A || typeof A.data !== 'function') return;

  A.data('countersSection', (rawTargets) => {
    const list = Array.isArray(rawTargets) ? rawTargets.map((n) => parseInt(n, 10) || 0) : [0, 0, 0];
    const targetNumbers = [0, 1, 2].map((i) => (typeof list[i] === 'number' && !Number.isNaN(list[i]) ? list[i] : 0));

    return {
      displayNumbers: [0, 0, 0],
      targetNumbers,
      animationDuration: 2000,

      startCounters() {
        this.targetNumbers.forEach((target, index) => {
          if (target > 0) {
            this.animateCounter(index, target);
          }
        });
      },

      animateCounter(index, target) {
        const startTime = Date.now();
        const startValue = 0;
        const animate = () => {
          const elapsed = Date.now() - startTime;
          const progress = Math.min(elapsed / this.animationDuration, 1);
          const easeOutQuart = 1 - Math.pow(1 - progress, 4);
          this.displayNumbers[index] = Math.floor(startValue + (target - startValue) * easeOutQuart);
          if (progress < 1) {
            requestAnimationFrame(animate);
          } else {
            this.displayNumbers[index] = target;
          }
        };
        requestAnimationFrame(animate);
      },
    };
  });
});
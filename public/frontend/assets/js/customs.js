// ========================================
// UTILITY FUNCTIONS
// ========================================

const Utils = {
  showToast(message, duration = 3000) {
    const toast = document.getElementById('toast') || document.getElementById('copyToast');
    if (!toast) return;

    const span = toast.querySelector('span');
    if (span) {
      span.textContent = message;
    } else {
      toast.textContent = message;
    }

    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), duration);
  },

  copyToClipboard(text, successMessage = 'Copied to clipboard!') {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text)
        .then(() => this.showToast(successMessage))
        .catch(() => this.fallbackCopy(text, successMessage));
    } else {
      this.fallbackCopy(text, successMessage);
    }
  },

  fallbackCopy(text, successMessage) {
    const temp = document.createElement('textarea');
    temp.value = text;
    temp.style.position = 'fixed';
    temp.style.opacity = '0';
    document.body.appendChild(temp);
    temp.select();
    temp.setSelectionRange(0, 99999);

    try {
      document.execCommand('copy');
      this.showToast(successMessage);
    } catch (err) {
      this.showToast('Failed to copy');
    }

    document.body.removeChild(temp);
  },

  goBack() {
    window.history.back();
  }
};

// ========================================
// SLIDER MODULE
// ========================================

const Slider = {
  currentSlide: 0,
  slides: [],
  totalSlides: 0,
  sliderTrack: null,
  dotsContainer: null,
  autoSlideInterval: null,

  init() {
    this.slides = document.querySelectorAll('.slide');
    this.totalSlides = this.slides.length;
    this.sliderTrack = document.getElementById('sliderTrack');
    this.dotsContainer = document.getElementById('sliderDots');

    if (!this.sliderTrack || !this.dotsContainer || this.totalSlides === 0) return;

    this.createDots();
    this.setupAutoSlide();
    this.setupTouchSwipe();
  },

  createDots() {
    this.dotsContainer.innerHTML = '';
    for (let i = 0; i < this.totalSlides; i++) {
      const dot = document.createElement('div');
      dot.className = `dot${i === 0 ? ' active' : ''}`;
      dot.onclick = () => this.goToSlide(i);
      this.dotsContainer.appendChild(dot);
    }
  },

  moveSlide(direction) {
    this.currentSlide += direction;
    if (this.currentSlide < 0) this.currentSlide = this.totalSlides - 1;
    if (this.currentSlide >= this.totalSlides) this.currentSlide = 0;
    this.updateSlider();
  },

  goToSlide(index) {
    this.currentSlide = index;
    this.updateSlider();
  },

  updateSlider() {
    this.sliderTrack.style.transform = `translateX(-${this.currentSlide * 100}%)`;
    document.querySelectorAll('.dot').forEach((dot, index) => {
      dot.classList.toggle('active', index === this.currentSlide);
    });
  },

  setupAutoSlide() {
    const container = document.querySelector('.slider-container');
    if (!container) return;

    this.autoSlideInterval = setInterval(() => this.moveSlide(1), 4000);

    container.addEventListener('mouseenter', () => {
      if (this.autoSlideInterval) {
        clearInterval(this.autoSlideInterval);
      }
    });

    container.addEventListener('mouseleave', () => {
      this.autoSlideInterval = setInterval(() => this.moveSlide(1), 4000);
    });
  },

  setupTouchSwipe() {
    const container = document.querySelector('.slider-container');
    if (!container) return;

    let touchStartX = 0;
    let touchEndX = 0;

    container.addEventListener('touchstart', (e) => {
      touchStartX = e.changedTouches[0].screenX;
    });

    container.addEventListener('touchend', (e) => {
      touchEndX = e.changedTouches[0].screenX;
      const diff = touchStartX - touchEndX;

      if (Math.abs(diff) > 50) {
        this.moveSlide(diff > 0 ? 1 : -1);
      }
    });
  }
};

// ========================================
// SIDEBAR MODULE
// ========================================

const Sidebar = {
  sidebar: null,
  overlay: null,

  init() {
    this.sidebar = document.getElementById('sidebar');
    this.overlay = document.getElementById('sidebarOverlay');

    if (!this.sidebar || !this.overlay) return;

    this.setupEventListeners();
  },

  toggle() {
    if (!this.sidebar || !this.overlay) return;
    this.sidebar.classList.toggle('active');
    this.overlay.classList.toggle('active');
  },

  close() {
    if (!this.sidebar || !this.overlay) return;
    this.sidebar.classList.remove('active');
    this.overlay.classList.remove('active');
  },

  setupEventListeners() {
    // Prevent background scroll when sidebar is open
    this.sidebar.addEventListener('touchmove', (e) => e.stopPropagation());

    // Close sidebar on overlay click
    this.overlay.addEventListener('click', () => this.close());

    // Close sidebar on escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') this.close();
    });

    // Sidebar item active state
    document.querySelectorAll('.sidebar-item').forEach(item => {
      item.addEventListener('click', function() {
        document.querySelectorAll('.sidebar-item').forEach(i => {
          i.style.background = '';
        });
        this.style.background = 'rgba(102, 126, 234, 0.2)';
      });
    });

    // Menu icon click
    const menuIcon = document.querySelector('.menu-icon');
    if (menuIcon) {
      menuIcon.addEventListener('click', () => this.toggle());
    }
  },

  handleClick(section) {
    this.close();
    Utils.showToast(`${section} clicked`);
  }
};

// ========================================
// NAVIGATION MODULE
// ========================================

const Navigation = {
  init() {
    this.setupBottomNav();
    this.setupSmoothScroll();
    this.setupNotificationIcon();
  },

  handleBottomNav(section, element) {
    document.querySelectorAll('.nav-item').forEach(item => {
      item.classList.remove('active');
    });
    element.classList.add('active');
    Utils.showToast(`${section} selected`);
  },

  setupSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });
  },

  setupBottomNav() {
    document.querySelectorAll('.nav-item').forEach(item => {
      item.addEventListener('click', function() {
        document.querySelectorAll('.nav-item').forEach(i => {
          i.classList.remove('active');
        });
        this.classList.add('active');
      });
    });
  },

  setupNotificationIcon() {
    const notificationIcon = document.querySelector('.notification-icon');
    if (notificationIcon) {
      notificationIcon.addEventListener('click', () => {
        Utils.showToast('No new notifications');
      });
    }
  }
};

// ========================================
// PAYMENT MODULE
// ========================================

const Payment = {
  init() {
    this.setupPaymentForms();
    this.setupDropdownClose();
  },

  toggleOnlineMode() {
    const checkbox = document.getElementById('onlineCheckbox');
    const sendMoneySection = document.getElementById('sendMoneySection');
    const onlineSection = document.getElementById('onlineSection');
    const paymentSection = document.getElementById('paymentSection');

    if (!checkbox) return;

    if (checkbox.checked) {
      if (sendMoneySection) sendMoneySection.style.display = 'none';
      if (onlineSection) onlineSection.style.display = 'block';
      if (paymentSection) paymentSection.style.display = 'none';
    } else {
      if (sendMoneySection) sendMoneySection.style.display = 'block';
      if (onlineSection) onlineSection.style.display = 'none';
      if (paymentSection) paymentSection.style.display = 'block';
    }
  },

  toggleDropdown() {
    const menu = document.getElementById('dropdownMenu');
    const btn = document.querySelector('.send-money-btn');
    if (menu && btn) {
      menu.classList.toggle('show');
      btn.classList.toggle('active');
    }
  },

  selectPaymentMethod(method) {
    const forms = ['bkashForm', 'nagadForm', 'rocketForm'];
    forms.forEach(formId => {
      const form = document.getElementById(formId);
      if (form) form.classList.remove('show');
    });

    const selectedForm = document.getElementById(`${method}Form`);
    if (selectedForm) selectedForm.classList.add('show');

    const labels = { bkash: 'Bkash', nagad: 'Nagad', rocket: 'Rocket' };

    const selectedMethod = document.getElementById('selectedMethod');
    const paymentTitle = document.getElementById('paymentTitle');

    if (selectedMethod) selectedMethod.innerText = labels[method] || method;
    if (paymentTitle) paymentTitle.innerText = labels[method] || method;

    this.toggleDropdown();
  },

  copyAccountNumber() {
    const accountNumber = document.getElementById('accountNumber');
    if (accountNumber) {
      Utils.copyToClipboard(accountNumber.innerText, 'Account number copied!');
    }
  },

  handleSubmit(event, method) {
    event.preventDefault();
    const messages = {
      bkash: 'Bkash payment submitted successfully!',
      nagad: 'Nagad payment submitted successfully!',
      rocket: 'Rocket payment submitted successfully!',
      online: 'Online payment submitted successfully!'
    };

  },

  setupPaymentForms() {
    const forms = document.querySelectorAll('form[id$="Form"]');
    forms.forEach(form => {
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        const method = form.id.replace('Form', '');
        this.handleSubmit(e, method);
      });
    });
  },

  setupDropdownClose() {
    document.addEventListener('click', (e) => {
      const menu = document.getElementById('dropdownMenu');
      const btn = document.querySelector('.send-money-btn');
      if (menu && btn && menu.classList.contains('show')) {
        if (!btn.contains(e.target) && !menu.contains(e.target)) {
          menu.classList.remove('show');
          btn.classList.remove('active');
        }
      }
    });
  }
};

// ========================================
// TAB MODULE
// ========================================

const Tabs = {
  switchTab(event, tabName) {
    document.querySelectorAll('.tab-btn').forEach(tab => {
      tab.classList.remove('active');
    });

    document.querySelectorAll('.tab-content').forEach(content => {
      content.classList.remove('active');
    });

    event.currentTarget.classList.add('active');

    const content = document.getElementById(tabName);
    if (content) content.classList.add('active');
  },

  openVideoTab(tabName) {
    const videoSection = document.getElementById('videoSection');
    if (!videoSection) return;

    videoSection.scrollIntoView({ behavior: 'smooth', block: 'start' });

    setTimeout(() => {
      document.querySelectorAll('.tab-btn').forEach(tab => {
        tab.classList.remove('active');
      });

      document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
      });

      document.querySelectorAll('.tab-btn').forEach(tab => {
        if (tab.textContent.toLowerCase().includes(tabName)) {
          tab.classList.add('active');
        }
      });

      const content = document.getElementById(tabName);
      if (content) content.classList.add('active');
    }, 500);
  }
};

// ========================================
// PROFILE MODULE
// ========================================

const Profile = {
  init() {
    this.setupPasswordToggle();
    this.setupPhotoPreview();
    this.setupFormSubmit();
  },

  setupPasswordToggle() {
    document.querySelectorAll('.passwordChange-toggle').forEach(icon => {
      icon.addEventListener('click', function() {
        const input = this.closest('.input-group').querySelector('input');
        if (!input) return;

        if (input.type === 'password') {
          input.type = 'text';
          const i = this.querySelector('i');
          if (i) i.classList.replace('fa-eye-slash', 'fa-eye');
        } else {
          input.type = 'password';
          const i = this.querySelector('i');
          if (i) i.classList.replace('fa-eye', 'fa-eye-slash');
        }
      });
    });
  },

  setupPhotoPreview() {
    const photoInput = document.getElementById('passwordChange-photo');
    const profileImage = document.getElementById('passwordChange-profileImage');

    if (photoInput && profileImage) {
      photoInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = (e) => {
            profileImage.src = e.target.result;
          };
          reader.readAsDataURL(file);
        }
      });
    }
  },


};

// ========================================
// SEARCH MODULE
// ========================================

const Search = {
  searchAgents() {
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearch');

    if (!searchInput) return;

    const searchTerm = searchInput.value.toLowerCase();
    const agentCards = document.querySelectorAll('.agent-card');

    if (clearBtn) {
      clearBtn.classList.toggle('show', searchTerm.length > 0);
    }

    let visibleCount = 0;

    agentCards.forEach(card => {
      const name = card.querySelector('.agent-name')?.textContent.toLowerCase() || '';
      const agentId = card.querySelector('.agent-id')?.textContent.toLowerCase() || '';
      const phone = card.querySelector('.detail-value')?.textContent.toLowerCase() || '';

      if (name.includes(searchTerm) || agentId.includes(searchTerm) || phone.includes(searchTerm)) {
        card.style.display = 'block';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    const sectionTitle = document.querySelector('.section-title');
    if (sectionTitle) {
      if (searchTerm.length > 0) {
        sectionTitle.innerHTML = `<i class="fas fa-users"></i> Search Results (${visibleCount})`;
      } else {
        sectionTitle.innerHTML = '<i class="fas fa-users"></i> Available Agents (5)';
      }
    }
  },

  clearSearch() {
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearch');

    if (searchInput) searchInput.value = '';
    if (clearBtn) clearBtn.classList.remove('show');

    this.searchAgents();
  }
};

// ========================================
// FAQ MODULE
// ========================================

const FAQ = {
  toggle(element) {
    const answer = element.nextElementSibling;
    const allQuestions = document.querySelectorAll('.faq-question');
    const allAnswers = document.querySelectorAll('.faq-answer');

    allQuestions.forEach(q => {
      if (q !== element) q.classList.remove('active');
    });

    allAnswers.forEach(a => {
      if (a !== answer) a.classList.remove('active');
    });

    element.classList.toggle('active');
    if (answer) answer.classList.toggle('active');
  }
};

// ========================================
// REFER MODULE
// ========================================

const Refer = {
  copyReferCode() {
    const referCode = document.getElementById('referCodeText');
    if (referCode) {
      Utils.copyToClipboard(referCode.textContent, 'Refer code copied!');
    }
  },

  copyReferLink() {
    const referLink = document.getElementById('referLink');
    if (!referLink) return;

    referLink.select();
    referLink.setSelectionRange(0, 99999);

    Utils.copyToClipboard(referLink.value, 'Refer link copied!');

    const btn = event.target.closest('.copy-btn');
    if (btn) {
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
      setTimeout(() => {
        btn.innerHTML = originalText;
      }, 2000);
    }
  }
};

// ========================================
// TASK/ADS MODULE
// ========================================

const Tasks = {
  tasksCompleted: 1,

  goToHome() {
    Utils.showToast('Going back to home page');
    // window.location.href = 'home.html';
  },

  goToAddedPage() {
    const taskPage = document.getElementById('taskPage');
    const addedPage = document.getElementById('addedPage');

    if (taskPage && addedPage) {
      taskPage.classList.remove('active');
      addedPage.classList.add('active');

      this.tasksCompleted = 2;

      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });

      // Optional: Create confetti effect
      this.createConfetti();
    }
  },

  backToTask() {
    const taskPage = document.getElementById('taskPage');
    const addedPage = document.getElementById('addedPage');
    const taskCounter = document.getElementById('taskCounter');

    if (taskPage && addedPage) {
      addedPage.classList.remove('active');
      taskPage.classList.add('active');

      if (taskCounter) taskCounter.textContent = '2/2';

      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }
  },

  createConfetti() {
    const colors = ['#ff6347', '#4caf50', '#2196f3', '#ffeb3b', '#e91e63'];
    for (let i = 0; i < 50; i++) {
      const confetti = document.createElement('div');
      confetti.style.position = 'fixed';
      confetti.style.width = '10px';
      confetti.style.height = '10px';
      confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
      confetti.style.left = Math.random() * window.innerWidth + 'px';
      confetti.style.top = '-10px';
      confetti.style.borderRadius = '50%';
      confetti.style.pointerEvents = 'none';
      confetti.style.zIndex = '9999';
      document.body.appendChild(confetti);

      const duration = Math.random() * 3 + 2;
      const xMovement = (Math.random() - 0.5) * 200;

      confetti.animate([
        { transform: 'translateY(0) translateX(0) rotate(0deg)', opacity: 1 },
        { transform: `translateY(${window.innerHeight}px) translateX(${xMovement}px) rotate(720deg)`, opacity: 0 }
      ], {
        duration: duration * 1000,
        easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)'
      });

      setTimeout(() => confetti.remove(), duration * 1000);
    }
  }
};

// ========================================
// UI ENHANCEMENTS
// ========================================

const UIEnhancements = {
  init() {
    this.addRippleEffect();
    this.preventDoubleTapZoom();
    this.setupLazyLoading();
    this.setupPageLoadAnimation();
    this.setupMenuCardAnimation();
  },

  addRippleEffect() {
    const style = document.createElement('style');
    style.textContent = `
      @keyframes ripple {
        to {
          transform: scale(2);
          opacity: 0;
        }
      }
    `;
    document.head.appendChild(style);

    document.querySelectorAll('.menu-card').forEach(card => {
      card.addEventListener('click', function(e) {
        const ripple = document.createElement('span');
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;

        Object.assign(ripple.style, {
          width: `${size}px`,
          height: `${size}px`,
          left: `${x}px`,
          top: `${y}px`,
          position: 'absolute',
          borderRadius: '50%',
          background: 'rgba(102, 126, 234, 0.3)',
          transform: 'scale(0)',
          animation: 'ripple 0.6s ease-out',
          pointerEvents: 'none'
        });

        this.appendChild(ripple);
        setTimeout(() => ripple.remove(), 600);
      });
    });
  },

  preventDoubleTapZoom() {
    let lastTouchEnd = 0;
    document.addEventListener('touchend', (e) => {
      const now = Date.now();
      if (now - lastTouchEnd <= 300) {
        e.preventDefault();
      }
      lastTouchEnd = now;
    }, false);
  },

  setupLazyLoading() {
    if ('IntersectionObserver' in window) {
      const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const img = entry.target;
            if (img.dataset.src) {
              img.src = img.dataset.src;
            }
            observer.unobserve(img);
          }
        });
      });

      document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
      });
    }
  },

  setupPageLoadAnimation() {
    window.addEventListener('load', () => {
      document.body.style.opacity = '0';
      setTimeout(() => {
        document.body.style.transition = 'opacity 0.5s ease';
        document.body.style.opacity = '1';
      }, 100);
    });
  },

  setupMenuCardAnimation() {
    document.querySelectorAll('.menu-card').forEach(card => {
      card.addEventListener('click', function() {
        this.style.transform = 'scale(0.95)';
        setTimeout(() => {
          this.style.transform = '';
        }, 100);
      });
    });
  }
};

// ========================================
// GLOBAL FUNCTIONS (for HTML onclick)
// ========================================

function toggleSidebar() { Sidebar.toggle(); }
function closeSidebar() { Sidebar.close(); }
function handleSidebarClick(section) { Sidebar.handleClick(section); }
function copyReferCode() { Refer.copyReferCode(); }
function copyReferLink() { Refer.copyReferLink(); }
function showToast(message) { Utils.showToast(message); }
function handleBottomNav(section, element) { Navigation.handleBottomNav(section, element); }
function handleMenuClick(section) { Utils.showToast(`Opening ${section}...`); }
function showNotification() { Utils.showToast('No new notifications'); }
function goBack() { Utils.goBack(); }
function toggleOnlineMode() { Payment.toggleOnlineMode(); }
function toggleDropdown() { Payment.toggleDropdown(); }
function selectPaymentMethod(method) { Payment.selectPaymentMethod(method); }
function copyAccountNumber() { Payment.copyAccountNumber(); }
function handleSubmit(event, method) { Payment.handleSubmit(event, method); }
function switchTab(event, tabName) { Tabs.switchTab(event, tabName); }
function openVideoTab(tabName) { Tabs.openVideoTab(tabName); }
function searchAgents() { Search.searchAgents(); }
function clearSearch() { Search.clearSearch(); }
function toggleFaq(element) { FAQ.toggle(element); }
function copyText(text) { Utils.copyToClipboard(text); }
function moveSlide(direction) { Slider.moveSlide(direction); }
function goToHome() { Tasks.goToHome(); }
function goToAddedPage() { Tasks.goToAddedPage(); }
function backToTask() { Tasks.backToTask(); }
function getStarted() {
  Utils.showToast('Redirecting to registration page...');
  // window.location.href = 'register.html';
}

// ========================================
// INITIALIZATION
// ========================================

document.addEventListener('DOMContentLoaded', () => {
  Slider.init();
  Sidebar.init();
  Navigation.init();
  Payment.init();
  Profile.init();
  UIEnhancements.init();
});

// Service Worker Registration (for PWA)
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    // Uncomment when service worker is ready
    // navigator.serviceWorker.register('/sw.js')
    //   .then(reg => console.log('SW registered', reg))
    //   .catch(err => console.log('SW error', err));
  });
      // Toggle Profile Details
    function toggleProfileDetails() {
        const profileDetails = document.getElementById('profileDetails');
        const tapArrow = document.getElementById('tapArrow');

        profileDetails.classList.toggle('show');
        tapArrow.classList.toggle('rotated');
    }

    // Copy Refer Code Function
    function copyReferCode(event) {
        // Stop propagation to prevent triggering parent click
        event.stopPropagation();

        const referCode = document.getElementById('referCodeText').textContent;

        navigator.clipboard.writeText(referCode).then(() => {
            showToast();
        }).catch(err => {
            console.error('Failed to copy:', err);
        });
    }

    // Show Toast Notification
    function showToast() {
        const toast = document.getElementById('toast');
        toast.classList.add('show');

        setTimeout(() => {
            toast.classList.remove('show');
        }, 2000);
    }
}

function toggleBalance() {
    const balanceDetails = document.getElementById('balanceDetails');
    const tapArrow = document.getElementById('tapArrow');

    balanceDetails.classList.toggle('show');
    tapArrow.classList.toggle('rotated');

    if (balanceDetails.classList.contains('show')) {
        // Hide automatically after 3 seconds
        setTimeout(() => {
            balanceDetails.classList.remove('show');
            tapArrow.classList.remove('rotated');
        }, 3000);
    }
}

// Copy Refer Code
function copyReferCode(event) {
    event.stopPropagation();
    const referCode = document.getElementById('referCodeText').textContent;

    navigator.clipboard.writeText(referCode).then(() => {
        showToast();
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}

// Toast Notification
function showToast() {
    const toast = document.getElementById('toast');
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2000);
}
 function selectPaymentMethod(method) {
    // Hide all forms
    document.querySelectorAll('.form-content').forEach(f => f.classList.remove('show'));
    // Show selected form
    document.getElementById(method + 'Form').classList.add('show');

    // Set hidden input
    document.getElementById('paymentMethodInput').value = method;

    // Button active class
    document.querySelectorAll('.method-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById(method + '-btn').classList.add('active');
}

function selectPaymentMethodFromCard(methodName, id) {
    // Optionally select corresponding button & form
    selectPaymentMethod(methodName);
    // Highlight card
    document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('active'));
    event.currentTarget.classList.add('active');
}

function copyNumber(number, btn) {
    navigator.clipboard.writeText(number).then(() => {
        btn.innerText = 'Copied';
        btn.classList.add('copied');
        setTimeout(() => {
            btn.innerText = 'Copy';
            btn.classList.remove('copied');
        }, 2000);
    });
}

// user chat

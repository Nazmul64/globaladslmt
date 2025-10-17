
        // Copy Refer Code Function
        function copyReferCode() {
            const referCode = document.getElementById('referCodeText').textContent;
            
            // Copy to clipboard
            navigator.clipboard.writeText(referCode).then(function() {
                // Show toast notification
                const toast = document.getElementById('copyToast');
                toast.classList.add('show');
                
                // Hide toast after 2 seconds
                setTimeout(function() {
                    toast.classList.remove('show');
                }, 2000);
            }).catch(function(err) {
                alert('Refer code: ' + referCode);
            });
        }

        // Sidebar Toggle Functions
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const menuIcon = document.querySelector('.menu-icon');

        function toggleSidebar() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        }

        menuIcon.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);

        function handleSidebarClick(menuName) {
            alert('You clicked: ' + menuName);
            toggleSidebar();
        }

        function handleMenuClick(menuName) {
            alert('You clicked: ' + menuName);
        }

        document.querySelectorAll('.menu-card').forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 100);
            });
        });

        document.querySelector('.notification-icon').addEventListener('click', function() {
            alert('Notifications clicked');
        });

        function handleBottomNav(page, element) {
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            element.classList.add('active');
            alert('Navigating to: ' + page);
        }

        // Slider Functionality
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const totalSlides = slides.length;
        const sliderTrack = document.getElementById('sliderTrack');
        const dotsContainer = document.getElementById('sliderDots');

        // Create dots
        for (let i = 0; i < totalSlides; i++) {
            const dot = document.createElement('div');
            dot.className = 'dot';
            if (i === 0) dot.classList.add('active');
            dot.onclick = () => goToSlide(i);
            dotsContainer.appendChild(dot);
        }

        function updateSlider() {
            sliderTrack.style.transform = `translateX(-${currentSlide * 100}%)`;
            
            // Update dots
            document.querySelectorAll('.dot').forEach((dot, index) => {
                dot.classList.toggle('active', index === currentSlide);
            });
        }

        function moveSlide(direction) {
            currentSlide += direction;
            if (currentSlide < 0) currentSlide = totalSlides - 1;
            if (currentSlide >= totalSlides) currentSlide = 0;
            updateSlider();
        }

        function goToSlide(index) {
            currentSlide = index;
            updateSlider();
        }

        // Auto slide every 4 seconds
        setInterval(() => {
            moveSlide(1);
        }, 4000);

// ads javascript
  // Track task completion
        let tasksCompleted = 1;

        // Navigation Functions
        function goToHome() {
            alert('Going back to home page');
            // আপনার মূল পেজে ফিরে যাওয়ার কোড এখানে লিখুন
            // window.location.href = 'home.html';
        }

        function goToAddedPage() {
            // Hide task page, show added page
            document.getElementById('taskPage').classList.remove('active');
            document.getElementById('addedPage').classList.add('active');
            
            // Update task count
            tasksCompleted = 2;
            
            // Scroll to top smoothly
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });

            // Play success sound (optional - uncomment if you have audio)
            // const audio = new Audio('success.mp3');
            // audio.play();
        }

        function backToTask() {
            // Hide added page, show task page
            document.getElementById('addedPage').classList.remove('active');
            document.getElementById('taskPage').classList.add('active');
            
            // Update counter to 2/2
            document.getElementById('taskCounter').textContent = '2/2';
            
            // Scroll to top smoothly
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Optional: Add confetti effect on success
        function createConfetti() {
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
           // Copy Text
        function copyText(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Copied to clipboard!');
            });
        }
        // reffer copy
        function copyReferLink() {
    const referLink = document.getElementById('referLink');
    referLink.select();
    referLink.setSelectionRange(0, 99999);
    document.execCommand('copy');
    
    const btn = event.target.closest('.copy-btn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    
    setTimeout(() => {
        btn.innerHTML = originalText;
    }, 2000);
}

        // Uncomment to add confetti effect on task completion
        // Modify goToAddedPage function to call createConfetti()

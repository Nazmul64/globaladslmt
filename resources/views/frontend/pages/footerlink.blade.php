
<script>
// Start Task Modal Functions
function openTaskModal(event) {
    event.preventDefault();
    document.getElementById('startTaskModal').style.display = 'block';
}

function closeTaskModal() {
    document.getElementById('startTaskModal').style.display = 'none';
}

function goToAds() {
    window.location.href = 'ads.html';
}

// পেজ লোড হলে Start Task লিংকে ইভেন্ট যোগ করবে
document.addEventListener('DOMContentLoaded', function() {
    const startTaskLink = document.querySelector('a[href="ads.html"]');
    if (startTaskLink) {
        startTaskLink.addEventListener('click', openTaskModal);
    }
});

// মডালের বাইরে ক্লিক করলে বন্ধ হবে
window.addEventListener('click', function(event) {
    if (event.target.id === 'startTaskModal') {
        closeTaskModal();
    }
});
</script>
<div id="startio-banner"></div>
<script>
  if (typeof startio !== 'undefined' && typeof startio.display === 'function') {
      startio.display('startio-banner');
  }
</script>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{asset('frontend')}}/assets/js/customs.js"></script>
<script src="{{asset('frontend')}}/assets/js/custom.js"></script>
</body>
</html>

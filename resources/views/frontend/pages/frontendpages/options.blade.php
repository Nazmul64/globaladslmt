 @include('frontend.pages.header')
   <div class="header">
        <a href="#"><i class="fas fa-arrow-left back-btn" ></i></a>
        <div class="header-title">Options</div>
    </div>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
      <link rel="stylesheet" href="{{asset('frontend')}}/assets/css/custom.css">
    <!-- Container -->
    <div class="container">
        <!-- Add Balance Card -->
        <div class="option-card">
            <div class="icon-circle">
                <i class="fas fa-plus"></i>
            </div>
            <div class="option-title"><a href="addblance.html">Add Balance</a></div>
        </div>

        <!-- Buy Membership Card -->
        <div class="option-card">
            <div class="icon-circle">
                <i class="fas fa-shopping-basket"></i>
            </div>
            <div class="option-title"><a href="package.html">Buy Membership</a></div>
        </div>

        <!-- Info Card -->
        <div class="info-card">
            <div class="info-question">
                1) How to Buy Membership in Global Money Company?
            </div>
            <div class="info-answer">
                <strong>Ans:</strong> After clicking on <strong>Add Balance</strong> you will see many deposit balances. You can deposit in any account. Click on your profile after the deposit is complete. You can see if your deposit balance has been added. After the deposit balance is added. Click on <strong>Buy Membership</strong> to purchase the membership of your choice. Then click on <strong>Subscribe Now</strong> on the membership of your choice. Then confirm your membership purchase by clicking on <strong>Confirm</strong>. •º*"˜˜"*º•
            </div>
        </div>
    </div>
    @include('frontend.pages.footer')
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{asset('frontend')}}/assets/js/customs.js"></script>
    <script src="{{asset('frontend')}}/assets/js/custom.js"></script>


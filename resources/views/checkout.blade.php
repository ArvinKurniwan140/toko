<!DOCTYPE html>
<html lang="zxx">
<head>
	<title>The Plaza - eCommerce Template</title>
	<meta charset="UTF-8">
	<meta name="description" content="The Plaza eCommerce Template">
	<meta name="keywords" content="plaza, eCommerce, creative, html">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- Favicon -->   
	<link href="img/favicon.ico" rel="shortcut icon"/>

	<!-- Google Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Raleway:400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

	<!-- Stylesheets -->
	<link rel="stylesheet" href="css/bootstrap.min.css"/>
	<link rel="stylesheet" href="css/font-awesome.min.css"/>
	<link rel="stylesheet" href="css/owl.carousel.css"/>
	<link rel="stylesheet" href="css/style.css"/>
	<link rel="stylesheet" href="css/animate.css"/>


	<!--[if lt IE 9]>
	  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->

</head>
<body>
	<!-- Page Preloder -->
	<div id="preloder">
		<div class="loader"></div>
	</div>
	
	<!-- Header section -->
	<header class="header-section header-normal">
		<div class="container-fluid">
			<!-- logo -->
			<div class="site-logo">
				<img src="img/logo.png" alt="logo">
			</div>
			<!-- responsive -->
			<div class="nav-switch">
				<i class="fa fa-bars"></i>
			</div>
			<div class="header-right">
				<a href="cart.html" class="card-bag"><img src="img/icons/bag.png" alt=""><span>2</span></a>
				<a href="#" class="search"><img src="img/icons/search.png" alt=""></a>
			</div>
			<!-- site menu -->
			<ul class="main-menu">
				<li><a href="index.html">Home</a></li>
				<li><a href="#">Woman</a></li>
				<li><a href="#">Man</a></li>
				<li><a href="#">LookBook</a></li>
				<li><a href="#">Blog</a></li>
				<li><a href="contact.html">Contact</a></li>
			</ul>
		</div>
	</header>
	<!-- Header section end -->


	<!-- Page Info -->
	<div class="page-info-section page-info">
		<div class="container">
			<div class="site-breadcrumb">
				<a href="/">Home</a> / 
				<a href="">Sales</a> / 
				<a href="">Bags</a> / 
				<a href="">Cart</a> / 
				<span>Checkout</span>
			</div>
			<img src="img/page-info-art.png" alt="" class="page-info-art">
		</div>
	</div>
	<!-- Page Info end -->


	<!-- Page -->
	<div class="page-area checkout-page spad">
        <div class="container">
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
    
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
    
            <!-- Display form errors if any -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
    
            <form action="{{ route('order.store') }}" method="POST" id="checkout-form">
                @csrf
                <div class="row">
                    <div class="col-lg-8">
                        <div class="checkout-form-warp">
                            <h4 class="checkout-title">Billing Details</h4>
                            <div class="checkout-form">
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" name="first_name" placeholder="First Name *" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="last_name" placeholder="Last Name *" required>
                                    </div>
                                    <div class="col-md-12">
                                        <input type="text" name="company" placeholder="Company Name">
                                    </div>
                                    <div class="col-md-12">
                                        <input type="text" name="address" placeholder="Address *" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="city" placeholder="City *" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="zipcode" placeholder="Zip code *" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="country" placeholder="Country *" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="province" placeholder="province *" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="phone" placeholder="Phone no *" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="email" name="email" placeholder="Email *" required>
                                    </div>
                                    <div class="col-md-12">
                                        <input type="checkbox" name="create_account" id="create_account">
                                        <label for="create_account">Create an account</label>
                                    </div>
                                    <div class="col-md-12">
                                        <textarea name="notes" placeholder="Order Notes"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 order-summary">
                        <div class="checkout-card">
                            <h4 class="checkout-title">Your Order</h4>
                            <div class="checkout-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cart as $item)
                                        <tr>
                                            <td>{{ $item->product->name }} <span>× {{ $item->quantity }}</span></td>
                                            <td>${{ number_format($item->product->price * $item->quantity, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="subtotal">
                                            <td>Subtotal</td>
                                            <td>${{ number_format($subtotal, 2) }}</td>
                                        </tr>
                                        <tr class="shipping">
                                            <td>Shipping</td>
                                            <td>
                                                @if($shipping > 0)
                                                    ${{ number_format($shipping, 2) }}
                                                @else
                                                    Free
                                                @endif
                                            </td>
                                        </tr>
                                        <tr class="total">
                                            <td>Total</td>
                                            <td>${{ number_format($total, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="payment-method">
                                <div class="pm-item">
                                    <input type="radio" name="payment_method" id="card" value="card" checked>
                                    <label for="card">Credit / Debit card</label>
                                </div>
                                <div class="pm-item">
                                    <input type="radio" name="payment_method" id="paypal" value="paypal">
                                    <label for="paypal">PayPal</label>
                                </div>
                            </div>
                            <button class="site-btn btn-full" type="submit">Place Order</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
	<!-- Page -->


	<!-- Footer top section -->	
	<section class="footer-top-section home-footer">
		<div class="container">
			<div class="row">
				<div class="col-lg-3 col-md-8 col-sm-12">
					<div class="footer-widget about-widget">
						<img src="img/logo.png" class="footer-logo" alt="">
						<p>Donec vitae purus nunc. Morbi faucibus erat sit amet congue mattis. Nullam fringilla faucibus urna, id dapibus erat iaculis ut. Integer ac sem.</p>
						<div class="cards">
							<img src="img/cards/5.png" alt="">
							<img src="img/cards/4.png" alt="">
							<img src="img/cards/3.png" alt="">
							<img src="img/cards/2.png" alt="">
							<img src="img/cards/1.png" alt="">
						</div>
					</div>
				</div>
				<div class="col-lg-2 col-md-4 col-sm-6">
					<div class="footer-widget">
						<h6 class="fw-title">usefull Links</h6>
						<ul>
							<li><a href="#">Partners</a></li>
							<li><a href="#">Bloggers</a></li>
							<li><a href="#">Support</a></li>
							<li><a href="#">Terms of Use</a></li>
							<li><a href="#">Press</a></li>
						</ul>
					</div>
				</div>
				<div class="col-lg-2 col-md-4 col-sm-6">
					<div class="footer-widget">
						<h6 class="fw-title">Sitemap</h6>
						<ul>
							<li><a href="#">Partners</a></li>
							<li><a href="#">Bloggers</a></li>
							<li><a href="#">Support</a></li>
							<li><a href="#">Terms of Use</a></li>
							<li><a href="#">Press</a></li>
						</ul>
					</div>
				</div>
				<div class="col-lg-2 col-md-4 col-sm-6">
					<div class="footer-widget">
						<h6 class="fw-title">Shipping & returns</h6>
						<ul>
							<li><a href="#">About Us</a></li>
							<li><a href="#">Track Orders</a></li>
							<li><a href="#">Returns</a></li>
							<li><a href="#">Jobs</a></li>
							<li><a href="#">Shipping</a></li>
							<li><a href="#">Blog</a></li>
						</ul>
					</div>
				</div>
				<div class="col-lg-2 col-md-4 col-sm-6">
					<div class="footer-widget">
						<h6 class="fw-title">Contact</h6>
						<div class="text-box">
							<p>Your Company Ltd </p>
							<p>1481 Creekside Lane  Avila Beach, CA 93424, </p>
							<p>+53 345 7953 32453</p>
							<p>office@youremail.com</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- Footer top section end -->	


	<!-- Footer section -->
	<footer class="footer-section">
		<div class="container">
			<p class="copyright">
<!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved | This template is made with <i class="fa fa-heart-o" aria-hidden="true"></i> by <a href="https://colorlib.com" target="_blank">Colorlib</a>
<!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
</p>
		</div>
	</footer>
	<!-- Footer section end -->


	<!--====== Javascripts & Jquery ======-->
	<script src="js/jquery-3.2.1.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/owl.carousel.min.js"></script>
	<script src="js/mixitup.min.js"></script>
	<script src="js/sly.min.js"></script>
	<script src="js/jquery.nicescroll.min.js"></script>
	<script src="js/main.js"></script>
    </body>
</html>
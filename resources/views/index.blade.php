<!DOCTYPE html>
<html lang="zxx">

<head>
    <title>The Plaza - eCommerce Template</title>
    <meta charset="UTF-8">
    <meta name="description" content="The Plaza eCommerce Template">
    <meta name="keywords" content="plaza, eCommerce, creative, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon -->
    <link href="{{ asset('img/favicon.ico') }}" rel="shortcut icon" />

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/animate.css') }}" />


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
    <header class="header-section">
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
                
                <a href="cart" class="card-bag"><img src="img/icons/bag.png" alt=""><span>2</span></a>
                <a href="#" class="search"><img src="img/icons/search.png" alt=""></a>
                @guest
                <a href="login" class="alert alert-light ml-5">Login</a>
                    
                @endguest
                @auth
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-outline-light ml-3">Logout</button>
                </form>
                @endauth
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


    <!-- Hero section -->
    <section class="hero-section set-bg" data-setbg="img/bg.jpg">
        <div class="hero-slider owl-carousel">
            <div class="hs-item">
                <div class="hs-left"><img src="img/slider-img.png" alt=""></div>
                <div class="hs-right">
                    <div class="hs-content">
                        <div class="price">from $19.90</div>
                        <h2><span>2018</span> <br>summer collection</h2>
                        <a href="" class="site-btn">Shop NOW!</a>
                    </div>
                </div>
            </div>
            <div class="hs-item">
                <div class="hs-left"><img src="img/slider-img.png" alt=""></div>
                <div class="hs-right">
                    <div class="hs-content">
                        <div class="price">from $19.90</div>
                        <h2><span>2018</span> <br>summer collection</h2>
                        <a href="" class="site-btn">Shop NOW!</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Hero section end -->


    <!-- Intro section -->
    <section class="intro-section spad pb-0">
        <div class="section-title">
            <h2>premium products</h2>
            <p>We recommend</p>
        </div>
        <div class="intro-slider">
            <ul class="slidee">
                @foreach($premiumProducts as $product)
                <li>
                    <div class="intro-item">
                        <figure>
                            <img src="{{ asset('img/products/' . $product->image) }}" alt="{{ $product->name }}">
                            @if($product->is_new)
                                <div class="bache">NEW</div>
                            @endif
                        </figure>
                        <div class="product-info">
                            <h5>{{ $product->name }}</h5>
                            <p>${{ number_format($product->price, 2) }}</p>
                            <form action="{{ route('cart.add', $product->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="site-btn btn-line">ADD TO CART</button>
                            </form>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        <div class="container">
            <div class="scrollbar">
                <div class="handle">
                    <div class="mousearea"></div>
                </div>
            </div>
        </div>
    </section>
    <!-- Intro section end -->


    <!-- Featured section -->
    <div class="featured-section spad">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="featured-item">
                        <img src="img/featured/featured-1.jpg" alt="">
                        <a href="#" class="site-btn">see more</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="featured-item mb-0">
                        <img src="img/featured/featured-2.jpg" alt="">
                        <a href="#" class="site-btn">see more</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Featured section end -->


    <!-- Product section -->
    <section class="product-section spad">
        <div class="container">
            <ul class="product-filter controls">
                <li class="control" data-filter=".new">New arrivals</li>
                <li class="control" data-filter="all">Recommended</li>
                <li class="control" data-filter=".best">Best sellers</li>
            </ul>
            <div class="row" id="product-filter">
                @foreach($allProducts as $product)
                <div class="mix col-lg-3 col-md-6 {{ $product->category }}">
                    <div class="product-item">
                        <figure>
                            <img src="{{ asset('img/products/' . $product->image) }}" alt="{{ $product->name }}">
                            @if($product->is_new)
                                <div class="bache">NEW</div>
                            @elseif($product->is_sale)
                                <div class="bache sale">SALE</div>
                            @endif
                            <div class="pi-meta">
                                <div class="pi-m-left">
                                    <img src="{{ asset('img/icons/eye.png') }}" alt="">
                                    <p>quick view</p>
                                </div>
                                <div class="pi-m-right">
                                    <img src="{{ asset('img/icons/heart.png') }}" alt="">
                                    <p>save</p>
                                </div>
                            </div>
                        </figure>
                        <div class="product-info">
                            <h6>{{ $product->name }}</h6>
                            @if($product->sale_price)
                                <p>${{ number_format($product->sale_price, 2) }} <span>RRP ${{ number_format($product->price, 2) }}</span></p>
                            @else
                                <p>${{ number_format($product->price, 2) }}</p>
                            @endif
                            <form action="{{ route('cart.add', $product->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="site-btn btn-line">ADD TO CART</button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    <!-- Product section end -->


    <!-- Blog section -->
    <section class="blog-section spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-5">
                    <div class="featured-item">
                        <img src="img/featured/featured-3.jpg" alt="">
                        <a href="#" class="site-btn">see more</a>
                    </div>
                </div>
                <div class="col-lg-7">
                    <h4 class="bgs-title">from the blog</h4>
                    <div class="blog-item">
                        <div class="bi-thumb">
                            <img src="img/blog-thumb/1.jpg" alt="">
                        </div>
                        <div class="bi-content">
                            <h5>10 tips to dress like a queen</h5>
                            <div class="bi-meta">July 02, 2018 | By maria deloreen</div>
                            <a href="#" class="readmore">Read More</a>
                        </div>
                    </div>
                    <div class="blog-item">
                        <div class="bi-thumb">
                            <img src="img/blog-thumb/2.jpg" alt="">
                        </div>
                        <div class="bi-content">
                            <h5>Fashion Outlet products</h5>
                            <div class="bi-meta">July 02, 2018 | By Jessica Smith</div>
                            <a href="#" class="readmore">Read More</a>
                        </div>
                    </div>
                    <div class="blog-item">
                        <div class="bi-thumb">
                            <img src="img/blog-thumb/3.jpg" alt="">
                        </div>
                        <div class="bi-content">
                            <h5>the little black dress just for you</h5>
                            <div class="bi-meta">July 02, 2018 | By maria deloreen</div>
                            <a href="#" class="readmore">Read More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Blog section end -->


    <!-- Footer top section -->
    <section class="footer-top-section home-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-8 col-sm-12">
                    <div class="footer-widget about-widget">
                        <img src="img/logo.png" class="footer-logo" alt="">
                        <p>Donec vitae purus nunc. Morbi faucibus erat sit amet congue mattis. Nullam fringilla faucibus
                            urna, id dapibus erat iaculis ut. Integer ac sem.</p>
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
                            <p>1481 Creekside Lane Avila Beach, CA 93424, </p>
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
                Copyright &copy;
                <script>document.write(new Date().getFullYear());</script> All rights reserved | This template is made
                with <i class="fa fa-heart-o" aria-hidden="true"></i> by <a href="https://colorlib.com"
                    target="_blank">Colorlib</a>
                <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
            </p>
        </div>
    </footer>
    <!-- Footer section end -->


    <!--====== Javascripts & Jquery ======-->
    <script src="{{ asset('js/jquery-3.2.1.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('js/mixitup.min.js') }}"></script>
    <script src="{{ asset('js/sly.min.js') }}"></script>
    <script src="{{ asset('js/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
</body>

</html>
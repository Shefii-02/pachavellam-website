(function ($) {
    'use strict';

    var newstenWindow = $(window);
    var sideNavWrapper = $("#sidenavWrapper");
    var blackOverlay = $(".sidenav-black-overlay");

    // :: Preloader
    // newstenWindow.on('load', function () {
    //     $('#preloader').fadeOut('200', function () {
    //         $(this).remove();
    //     });
    // });

    // :: Navbar
    $("#newstenNavbarToggler").on("click", function () {
        sideNavWrapper.addClass("nav-active");
        blackOverlay.addClass("active");
    });

    $("#goHomeBtn").on("click", function () {
        sideNavWrapper.removeClass("nav-active");
        blackOverlay.removeClass("active");
    });

    blackOverlay.on("click", function () {
        $(this).removeClass("active");
        sideNavWrapper.removeClass("nav-active");
    })

    // :: Comment Reply Form
    $(".reply-comment-btn").on("click", function () {
        $(".reply-comment-form").toggleClass("show");
    });

    // :: Hero Slides
    if ($.fn.owlCarousel) {
        var welcomeSlider = $('.hero-slides');
        welcomeSlider.owlCarousel({
            items:2,   
            dots: false,
            responsive:{
                0:{
                    items:2,
                    mouseDrag: true,
                    touchDrag: true
                },
                480:{
                    items:2,
                    mouseDrag: true,
                    touchDrag: true
                },
                750:{
                    items:4,
                    mouseDrag: true,
                    touchDrag: true
                },
                1000:{
                    items:4,
                    dots: false,
                    nav: false,
                    mouseDrag: true,
                    touchDrag: true
                }
            },
            loop: true,
            autoplay: true,
            
            margin: 0,
            nav: false,
            smartSpeed: 1000,
            autoplayTimeout: 3500
        })
    }

    // :: Catagory Slides
    if ($.fn.owlCarousel) {
        var catagorySlide = $('.catagory-slides');
        catagorySlide.owlCarousel({
            items: 3,
            dots: true,
            responsive:{
                0:{
                    items:2,
                    mouseDrag: true,
                    touchDrag: true
                },
                480:{
                    items:2,
                    mouseDrag: true,
                    touchDrag: true
                },
                750:{
                    items:2,
                    mouseDrag: true,
                    touchDrag: true
                },
                1000:{
                    items:4,
                    dots: false,
                    // nav: false,
                    // mouseDrag: false,
                    // touchDrag: false
                }
            },
            margin: 15,
            loop: true,
            // autoplay: true,
            smartSpeed: 1000,
            autoplayTimeout: 2000,
            dots: true,
            nav: false,
            
        })
    }
 
    // :: Editorial Slides
    if ($.fn.owlCarousel) {
        var editorialslides = $('.editorial-choice-news-slide');
        editorialslides.owlCarousel({
            items: 2,  
            dots: true,
            responsive:{
                0:{
                    items:2,
                    mouseDrag: true,
                    touchDrag: true
                },
                480:{
                    items:2,
                    mouseDrag: true,
                    touchDrag: true
                },
                750:{
                    items:2,
                    mouseDrag: true,
                    touchDrag: true
                },
                1000:{
                    items:4,
                    dots: false,
                    nav: false,
                    mouseDrag: false,
                    touchDrag: false
                }
            },
            margin: 15,
            loop: true,
            autoplay: true,
            smartSpeed: 1000,
            autoplayTimeout: 2000,
            dots: true,
            nav: false,
            animateIn: 'fadeIn',
            animateOut: 'fadeOut'
        })
    }

    // :: Owl Carousel Slides
    if ($.fn.owlCarousel) {
        var newstenowlslides = $('.newsten-owl-carousel-slides');
        newstenowlslides.owlCarousel({
            items: 2,
            responsive:{
                0:{
                    items:2,
                    mouseDrag: true,
                    touchDrag: true
                },
                480:{
                    items:2,
                    mouseDrag: true,
                    touchDrag: true
                },
                750:{
                    items:2,
                    mouseDrag: true,
                    touchDrag: true
                },
                1000:{
                    items:3,
                    dots: false,
                    nav: false,
                    mouseDrag: false,
                    touchDrag: false
                }
            },
            margin: 15,
            loop: true,
            autoplay: true,
            smartSpeed: 1000,
            autoplayTimeout: 2000,
            dots: false,
            nav: false
            // navText: [('<i class="fa fa-angle-left"></i>'), ('<i class="fa fa-angle-right"></i>')]
        })
    }

    // :: Tooltip
    if ($.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
    }

    // :: Jarallax
    if ($.fn.jarallax) {
        $('.jarallax').jarallax({
            speed: 0.5
        });
    }

    // :: Counter Up
    if ($.fn.counterUp) {
        $('.counter').counterUp({
            delay: 150,
            time: 3000
        });
    }

    // :: Prevent Default 'a' Click
    $('a[href="#"]').on('click', function ($) {
        $.preventDefault();
    });

    // :: Password Strength Active Code
    if ($.fn.passwordStrength) {
        $('#registerPassword').passwordStrength({
            minimumChars: 8
        });
    }

    // :: Animated Headline Active Code
    if ($.fn.animatedHeadline) {
        $('.built-with-selector').animatedHeadline();
    }
    
})(jQuery);
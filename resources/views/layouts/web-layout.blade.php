<!DOCTYPE html>
<html lang="en">

<head>
    <title>Pachavellam Education</title>
    <link rel="icon" href="{{ url('assets/images/favicon.png') }}" type="image/png" sizes="20x20" />
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ url('assets/images/favicon.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta property="og:image" content="{{ url('assets/images/favicon.png') }}" />
    <meta property="og:image:type" content="image/png" />
    <meta property="og:image:width" content="400" />
    <meta property="og:image:height" content="400" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta name="author" content="pachavellam.com">
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description"
        content="മത്സര പരീക്ഷകൾക്ക് തയ്യാറെടുക്കുന്ന ഉദ്യോഗാർത്ഥികൾക്ക് പഠിച്ചിരിക്കേണ്ട അറിവുകൾ, ആവശ്യമായ നിർദ്ദേശങ്ങൾ, വാർത്തകൾ, സിലബസുകൾ ഇതെല്ലാം നിങ്ങൾക്കിനി പച്ചവെള്ളംപോലെ.." />
    <meta name="keywords" content="Top Education,PSC Class Free,Free Class,Psc Class " />
    <meta name="p:domain_verify" content="598bc9f4edd54f0eba823b41efe4ac49" />
    <link rel="canonical" href="https://www.pachavellam.com/" />

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;700&amp;family=Roboto:wght@400;500;700&amp;display=swap">

    <!-- Plugins CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/font-awesome/css/all.min.css') }}">
    <!--<link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}">-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/glightbox/css/glightbox.css') }}">
    <!-- Theme CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style-dark.css') }}">
    <!-- Theme CSS -->
    <link id="style-switch" rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.css') }}">
    <!-- Include stylesheet -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .text_limit {
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            /* number of lines to show */
            line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .btn-circle {
            width: 30px;
            height: 30px;
            padding: 6px 0;
            border-radius: 15px;
            text-align: center;
            font-size: 12px;
            line-height: 1.428571429;
        }

        #editor-container {
            min-height: 375px;
        }

        @media (max-width: 991.98px) {
            .navbar-expand-lg .navbar-brand .navbar-brand-item {
                height: 44px;
            }
        }

        @media (min-width: 992px) {
            .navbar-expand-lg .navbar-brand .navbar-brand-item {
                height: 80px;
            }
        }

        @media (min-width: 1200px) {
            header.navbar-sticky-on .navbar-brand .navbar-brand-item {
                height: 60px;
            }
        }


        /* width */
        ::-webkit-scrollbar {
            width: 5px;
        }

        /* Track */
        ::-webkit-scrollbar-track {
            box-shadow: inset 0 0 5px grey;
            border-radius: 10px;
        }

        /* Handle */
        ::-webkit-scrollbar-thumb {
            background-color: #FFF;
            background-image: -webkit-linear-gradient(top,
                    #e4f5fc 0%,
                    #bfe8f9 50%,
                    #9fd8ef 51%,
                    #2aed75 100%);
            border-radius: 10px;
        }

        /* Handle on hover */
        ::-webkit-scrollbar-thumb:hover {
            background: #0089b3;
        }

        .loader {
            border: 16px solid #000000;
            border-radius: 50%;
            border-top: 16px solid blue;
            border-right: 16px solid green;
            border-bottom: 16px solid red;
            width: 120px;
            height: 120px;
            -webkit-animation: spin 2s linear infinite;
            animation: spin 2s linear infinite;
        }

        @-webkit-keyframes spin {
            0% {
                -webkit-transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
    @yield('styles')


    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-TBNJK6JTCL"></script>

    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-TBNJK6JTCL');
    </script>

    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-NHXSBF6');
    </script>
    <!-- End Google Tag Manager -->


</head>

<body>
    <main>
        @yield('content')
    </main>
    <div id="wait" style="width:69px;height:89px;position:fixed;z-index:99999;top:50%;left:50%;padding:2px;">
        <div class="loader"></div>
    </div>
</body>
<!-- Back to top -->
<div class="back-top"><i class="bi bi-arrow-up-short position-absolute top-50 start-50 translate-middle"></i></div>

<script src="{{ asset('assets/jquery.min.js') }}"></script>
<!-- Bootstrap JS -->
<script src="{{ asset('assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
<!-- Include the Quill library -->

<!-- Vendors -->
<script src="{{ asset('assets/vendor/isotope/isotope.pkgd.min.js') }}"></script>
<script src="{{ asset('assets/vendor/imagesLoaded/imagesloaded.js') }}"></script>
<script src="{{ asset('assets/vendor/glightbox/js/glightbox.js') }}"></script>
<script src="{{ asset('assets/vendor/purecounterjs/dist/purecounter_vanilla.js') }}"></script>

<!-- Template Functions -->
<script src="{{ asset('assets/js/functions.js') }}"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

{{-- <script src="{{ asset('assets/jquery.min.js') }}"></script> --}}

<script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.ckeditor').ckeditor();

    });


    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "preventDuplicates": false,
        "positionClass": "toast-top-right", // Toast position toast-top-right
        "timeOut": "5000", // Timeout duration
        "extendedTimeOut": "5000",
    };

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            toastr.error("{{ $error }}", "Error");
        @endforeach
    @endif
</script>



<script>
    @if (Session::has('message'))

        toastr.success("{{ session('message') }}");
    @endif

    @if (Session::has('error'))

        toastr.error("{{ session('error') }}");
    @endif

    @if (Session::has('info'))

        toastr.info("{{ session('info') }}");
    @endif

    @if (Session::has('warning'))

        toastr.warning("{{ session('warning') }}");
    @endif
</script>
<script>
    $(document).ready(function() {
        $("#wait").css("display", "none");;
    });
    $(document).ajaxStart(function() {
        $("#wait").css("display", "block");
    });

    $(document).ajaxComplete(function() {
        $("#wait").css("display", "none");
    });

    // CKEDITOR.replace( 'editor', {
    //     height: 400
    // } );

    //     var ck = CKEDITOR.replace('editor', {
    //         filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
    //         filebrowserUploadMethod: 'form',
    // 		height: 400,

    //     });

    CKEDITOR.config.allowedContent = true;
    var ck = CKEDITOR.replace('editor', {
        filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
        filebrowserUploadMethod: 'form',
        height: 400,

        filebrowserBrowseUrl: "{{ asset('ckeditor/filemanager/dialog.php') }}?type=2&editor=ckeditor&fldr=",
        filebrowserImageBrowseUrl: "{{ asset('ckeditor/filemanager/dialog.php') }}?type=1&editor=ckeditor&fldr=",

    });



    $('.my-editor').each(function() {
        CKEDITOR.replace($(this).attr('id'), {
            filebrowserUploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}",
            filebrowserUploadMethod: 'form',
            height: 100,
            filebrowserBrowseUrl: "{{ asset('ckeditor/filemanager/dialog.php') }}?type=2&editor=ckeditor&fldr=",
            filebrowserImageBrowseUrl: "{{ asset('ckeditor/filemanager/dialog.php') }}?type=1&editor=ckeditor&fldr=",
            toolbar: [
                {
                    name: 'basicstyles',
                    items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat']
                },
                {
                    name: 'paragraph',
                    items: ['NumberedList', 'BulletedList', '-', 'Blockquote']
                },
                {
                    name: 'insert',
                    items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar']
                }, // ✅ Enable image insertion
                
            ]
        });
    });
</script>
@yield('scripts')

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NHXSBF6" height="0" width="0"
        style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->


</body>

</html>

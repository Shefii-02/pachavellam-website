@section('styles')
<style>
    .social-share a {
            padding: 0 .2em;
        }
    .linked-list{
        color: #0e76a8;
    }
    .twitter{
        color: #00acee;
    }
    .bg1{
        background-image: linear-gradient(to top, #4481eb 0%, #04befe 100%);
    }
    .bg2{
        background-image: linear-gradient(to right, #3ab5b0 0%, #3d99be 31%, #56317a 100%);
    }
    .bg3{
        background-image: linear-gradient(-20deg, #b721ff 0%, #21d4fd 100%);
    }
</style>
@endsection
<div class=" col-lg-12">
    <p class="text-dark mob_text" style="line-height:2;">
        നിങ്ങൾക്ക് ഉപകാരപ്പെട്ടെങ്കിൽ നിങ്ങളുടെ കൂട്ടുകാർക്ക് കൂടി ഷെയർ ചെയ്യുക                        </p>
</div>

    
<div class='col-lg-12'>
    <p>Share This </p>
    <div class="social-share ">
        <a href="https://wa.me/?text={{url('/')}} നിങ്ങൾക്ക് ഉപകാരപ്പെട്ടെങ്കിൽ നിങ്ങളുടെ കൂട്ടുകാർക്ക് കൂടി ഷെയർ ചെയ്യുക" target='_blank' class="sharer button"><i class="fa fa-2x fa-whatsapp text-success "></i></a>
        <a href="https://www.facebook.com/sharer.php?u={{url('/')}}" target='_blank' class="sharer button"><i class="fa fa-2x fa-facebook-square text-primary"></i></a>
        <a href="https://twitter.com/intent/tweet?text= നിങ്ങൾക്ക് ഉപകാരപ്പെട്ടെങ്കിൽ നിങ്ങളുടെ കൂട്ടുകാർക്ക് കൂടി ഷെയർ ചെയ്യുക&url={{url('/')}}" target='_blank' class="sharer button"><i class="fa fa-2x fa-twitter-square twitter "></i></a>
        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{url('/')}}&title= നിങ്ങൾക്ക് ഉപകാരപ്പെട്ടെങ്കിൽ നിങ്ങളുടെ കൂട്ടുകാർക്ക് കൂടി ഷെയർ ചെയ്യുക&summary=&source={{url('/')}}" target='_blank' class="sharer button"><i class="fa fa-2x fa-linkedin-square linked-list "></i></a>
    </div><br>
    
</div>
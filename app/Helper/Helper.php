<?php
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



if(!function_exists('admin'))
{
	function kpsc_cms($url) {
		return url('admin/kpsc/'.$url);
	}
	function kpsc($url) {
		return url('kpsc/'.$url);
	}
	function ug_pg_cms($url){
		return url('admin/ug-pg/'.$url);
	}
	function teaching_cms($url){
		return url('admin/teaching/'.$url);
	}
	function admin_general($url){
		return url('admin/general/'.$url);
	}
	function student_cms($url){
		return url('admin/students/'.$url);
	}
	
}

    function ug_pg($url){
    	return url('ug-pg/'.$url);
    }
    
    function remove_slug($value) {
           
    	
    		return ucwords(str_replace('-',' ',$value));
    }
    
    //ads place spot
    
    function ads_space_sport($item_key){
        $item_key = $item_key+1;
        if(($item_key % 8 == 0)){
            
        
          return '<div class="col-lg-12 text-center">
                   <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2428477024574809"
                         crossorigin="anonymous"></script>
                    <!-- fixed-ad-hori-1 -->
                    <ins class="adsbygoogle"
                         style="display:block"
                         data-ad-client="ca-pub-2428477024574809"
                         data-ad-slot="5422510272"
                         data-ad-format="auto"
                         data-full-width-responsive="true"></ins>
                    <script>
                         (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </div>';
        }
  
    }
    
    function small_ads_space_sport($item_key){
         $item_key = $item_key+1;
        if(($item_key % 4 == 0)){
              return '<div class="col-lg-12 text-center">
                    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2428477024574809"
                             crossorigin="anonymous"></script>
                        <!-- fixed-ad-hori-1 -->
                        <ins class="adsbygoogle"
                             style="display:block"
                             data-ad-client="ca-pub-2428477024574809"
                             data-ad-slot="5422510272"
                             data-ad-format="auto"
                             data-full-width-responsive="true"></ins>
                        <script>
                             (adsbygoogle = window.adsbygoogle || []).push({});
                        </script>
                </div>';
        }
    }
    
    function bottom_single_ads(){
        return '<div class="col-lg-12 text-center">
                        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2428477024574809"
                             crossorigin="anonymous"></script>
                        <!-- mobile-bottom-ads -->
                        <ins class="adsbygoogle"
                             style="display:inline-block;width:320px;height:50px"
                             data-ad-client="ca-pub-2428477024574809"
                             data-ad-slot="9817390780"></ins>
                        <script>
                             (adsbygoogle = window.adsbygoogle || []).push({});
                        </script>
                </div>';
    }
    function bottom_double_ads(){
            
        
         return '<div class="col-lg-12 text-center">
                </div>';
    }
    
    function single_ads_show(){
        
        return '<div class="col-lg-12 text-center">
                   <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2428477024574809"
                         crossorigin="anonymous"></script>
                    <!-- fixed-ad-hori-1 -->
                    <ins class="adsbygoogle"
                         style="display:block"
                         data-ad-client="ca-pub-2428477024574809"
                         data-ad-slot="5422510272"
                         data-ad-format="auto"
                         data-full-width-responsive="true"></ins>
                    <script>
                         (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </div>';
    }
    

    function div_background(){
        $a=array("gradient","gradient","gradient");
        return $a[array_rand($a)];
    }
 
    //end section

    if(!function_exists('active_menu'))
    {
    	function active_menu($url) {
    	
    		if(request()->segment(3) == $url)	
    		
    			return 'active';
    	}
    }


    
    if(!function_exists('bottom_navbar'))
    {
    	function bottom_navbar($url) {

    		if(((request()->segment(1) == 'kpsc') || (request()->segment(2) == '')) || 
    		(request()->segment(2) == 'feed'))	
    		
    			return true;
    	}
    }






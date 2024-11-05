@extends('layouts.basic-links')
<style id="compiled-css" type="text/css">
/*  #originalText{*/
/* display: none;*/
/*}*/

#paginatedText{
  width: 100%;
  height: 500px;

}

.page,#originalText{
  /*padding: 3px;*/
  /*width: 98%;*/
  /*height: 498px;*/
  /*border: 1px solid #888;*/
  /*margin-bottom: 10px;*/
  /*line-height: 1.5em;*/
  /* color : rgba(70, 67, 67, 0.494); */
  background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' version='1.1' height='100px' width='100px'><text transform='translate(20, 20) rotate(-45)' fill='rgba(245,245,220);' font-size='6'>Pachavellam</text></svg>");
}

  /* EOS */
  
  img{
      max-width:100% !important;
  }
  
</style>

    @section('content')
    <div class="col-md-12">
      <div class="col-lg-4" style=" float: none;margin: 0 auto;">
           <div id="originalText">
            <h6>{!! strtoupper($list->title) !!}-{!! $list->year !!}</h6><br>
            
            {!! $list->content !!} 
          </div>
          <div id="paginatedText">

          </div>
      </div>
  </div>
   
   
     
     
<script type="text/javascript">//<![CDATA[
  
  
  function paginateText() {
      var text = document.getElementById("originalText").innerHTML; // gets the text, which should be displayed later on
      var textArray = text.split(" "); // makes the text to an array of words
      createPage(); // creates the first page
      for (var i = 0; i < textArray.length; i++) { // loops through all the words
          var success = appendToLastPage(textArray[i]); // tries to fill the word in the last page
          if (!success) { // checks if word could not be filled in last page
              createPage(); // create new empty page
              appendToLastPage(textArray[i]); // fill the word in the new last element
          }
      }
  }
  
  function createPage() {
      var page = document.createElement("div"); // creates new html element
      page.setAttribute("class", "page"); // appends the class "page" to the element
      document.getElementById("paginatedText").appendChild(page); // appends the element to the container for all the pages
  }
  
  function appendToLastPage(word) {
      var page = document.getElementsByClassName("page")[document.getElementsByClassName("page").length - 1]; // gets the last page
      var pageText = page.innerHTML; // gets the text from the last page
      page.innerHTML += word + " "; // saves the text of the last page
      if (page.offsetHeight < page.scrollHeight) { // checks if the page overflows (more words than space)
          page.innerHTML = pageText; //resets the page-text
          return false; // returns false because page is full
      } else {
          return true; // returns true because word was successfully filled in the page
      }
  }
  
//   paginateText();
  
  
    //]]></s>
  

  </script>  
   <script>
    $(document).ready(function(){
        
        $("<div class='mt-1 mb-1'>Google Ads Spot</div>").insertAfter("hr");
      
    });
    </script>
    @endsection

@extends('layouts.without-bottom')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC-Daily Exam List-" )
@php $title = "KPSC-Daily Exam List"; @endphp

@section('content')
  <div id="mobileB1">  
          <!-- mobile-bottom-ads -->
        <ins class="adsbygoogle"
             style="display:inline-block;width:320px;height:50px"
             data-ad-client="ca-pub-2428477024574809"
             data-ad-slot="9817390780"></ins>
        <script>
             (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>
    <div class="preloader" id="preloader" style="display:none">
      <div class="spinner-grow text-secondary" role="status">
        <div class="sr-only">Loading...</div>
      </div>
    </div>
    
<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
      <form action="{{url('kpsc/daily-exam/'.$exam_date.'/'.$exam_id)}}" id="daily_exam" method="POST" >
          @csrf()
          <input type="hidden" name="attempt" id="attempt" value="{{$attempt_id}}">
      </form>
      <div class="container">
           <div id="timer" class="float-right p-2">
              <span id="hours_timer" class="text-light">00:</span>
              <span id="mins" class="text-light">00:</span>
              <span id="seconds" class="text-light">00</span>  
            </div>
      </div>
      <div class="content">
        <div class="container-fluid">
        	
            <div class="header quiz-header bg-primary p-2">
              
              <div class="left-title">
                  
              </div>
              <div class="float-left text-light">Total Questions: <span id="tque"></span></div>
              <div class="clearfix"></div>
            </div>

        	<div class="row">
        		<div class="col-sm-12">
                	<div id="result" class="quiz-body mb-5">
                    <form name="quizForm" onSubmit="">
                    	<fieldset class="form-group">
                			<h4 class="text-dark"><span id="qid">1.</span> <span id="question" class="question_wraptext"></span></h4>
                            
                            <div class="option-block-container" id="question-options">
                              
                             </div> <!-- End of option block -->
                         </fieldset>
                         <!--<button  name="previous" id="previous" class="btn btn-success">Previous</button>-->
                            &nbsp;
                         &nbsp;
                         <button  name="next" id="next" class="btn btn-success next_btn">Next <i class="fa fa-arrow-right"></i></button>
                   </form>
                    <div class="col-lg-12 mt-5 mb-5">
                        
                      <!-- Ads -->
                        {!! single_ads_show() !!}
                    </div>
            
            
                   </div>
                </div> <!-- End of col-sm-12 -->
                
            </div> <!-- End of row -->
            
         
            
        </div> <!-- ENd of container fluid -->
    </div> <!-- End of content -->

    </div>
</div>
  
@endsection

@section('styles')

<style>
#result{
    background-color: #fff;
    padding: 15px;
    border-radius: 10px;
    height:auto;
    overflow:auto;
}
#question,#qid{
    color:#000;
        font-size: 14px;
}
#optionval{
  word-wrap: break-word;
}
.left-title { width:80px; color:#FFF; font-size*-:18px; float:left; }
.right-title { width:150px; text-align:right; float:right; color:#FFF;  }
.quiz-body { margin-top:15px; padding-bottom:50px; }
.option-block-container { margin-top:20px; max-width:420px; }
.option-block { padding:10px; background:aliceblue; border:1px solid #84c5fe; margin-bottom:10px; cursor:pointer; }
.result-question { font-weight:bold; }
.c-wrong { margin-left:20px; color:#FF0000; }
.c-correct {  margin-left:20px; color:green; }
.last-row { border-bottom:1px solid #ccc; padding-bottom:25px; margin-bottom:25px; }
.res-header { border-bottom:1px solid #ccc; margin-bottom:15px; padding-bottom:15px; }
body {
   
  background: linear-gradient(to bottom right, #38A3A5, #57CC99, #80ED99);

    background-size: cover;
    max-height: 100vh;
    /*overflow: hidden;*/
}

.question_wraptext{
    white-space: pre-wrap;
    line-height:1;
    
}

</style>

@endsection

@section('scripts')
<script>
$(document).ready(function(){
    var timer_hours =0;
    var timer_mins =0;
    var timer_seconds =0;
    var timex ;
    
      start__Timer();


    function start__Timer(){
      this.timex = setTimeout(function(){
          timer_seconds++;
        if(timer_seconds >59){
                timer_seconds=0;
                timer_mins++;
            if(timer_mins>59) {
                timer_mins=0;
                timer_hours++;
                if(timer_hours <10) {
                   
                     $("#hours_timer").text('0'+timer_hours+':')
                } 
                else{
                  
                    $("#hours_timer").text(timer_hours+':');
                }
            }
                           
        if(timer_mins<10){                     
          $("#mins").text('0'+timer_mins+':');}       
           else $("#mins").text(timer_mins+':');
                       }    
        if(timer_seconds <10) {
              $("#seconds").text('0'+timer_seconds);} else {
              $("#seconds").text(timer_seconds);
          }
         
        
          start__Timer();
      },1000);
    }
        
  
});

  
  

var quiz = {!! json_encode($date_based) !!};


var quizApp = function() {

	this.score = 0;		
	this.qno = 1;
	this.currentque = 0;
	this.wrongans  = 0;
	var totalque = quiz.JS.length;
    var last_one;
	
	this.displayQuiz = function(cque) {
		this.currentque = cque;
		
		
		    last_one = this.currentque;
			last_one = last_one+1;
			if(totalque == last_one){
			  
			    $('.next_btn').html(`Submit For Exam <i class="fa fa-arrow-right"></i>`);
			}
			else{
			     $('.next_btn').html(`Next <i class="fa fa-arrow-right"></i>`);
			}
			
	
		
		
		
		if(this.currentque <  totalque) {
			$("#tque").html(totalque);
// 			$("#previous").attr("disabled", false);
			$("#next").attr("disabled", false);
			$("#qid").html(quiz.JS[this.currentque].id + '.');
	
			
			$("#question").html(quiz.JS[this.currentque].question);	
			 $("#question-options").html("");
			for (var key in quiz.JS[this.currentque].options[0]) {
			  if (quiz.JS[this.currentque].options[0].hasOwnProperty(key)) {
		
				$("#question-options").append(
					"<div class='form-check option-block'>" +
					"<label class='form-check-label'>" +
							  "<input type='radio' class='form-check-input' name='option'   id='q"+key+"' value='" + quiz.JS[this.currentque].options[0][key] + "'><span id='optionval' class='text-dark font-weight-bold'>" +
								  quiz.JS[this.currentque].options[0][key] +
							 "</span></label>"
				);
			  }
			}
		}
		if(this.currentque <= 0) {
			$("#previous").attr("disabled", true);	
		}
		if(this.currentque >= totalque) {
				$('#next').attr('disabled', true);
				for(var i = 0; i < totalque; i++) {
					this.score = parseInt(this.score) + parseInt(quiz.JS[i].score);
					if(quiz.JS[i].score == "-0.333"){
					    this.wrongans = this.wrongans+1;
					}
				}
			clearTimeout(timex);
			return this.showResult(this.score - this.wrongans * 0.333);
		}
	}
	
	this.showResult = function(scr) {
		$("#result").addClass('result');
		$("#result").html("<h5 class='res-header font-weight-bold'>Total Score: &nbsp;" + scr  + '/' + totalque + "</h5>");
		for(var j = 0; j < totalque; j++) {
		    
			
			var res;
			if(quiz.JS[j].score == 0){
			    res = '<span class="wrong">' + quiz.JS[j].score + '</span>';
			}
			else if(quiz.JS[j].score == "-0.333") {
					res = '<span class="wrong">' + quiz.JS[j].score + '</span><i class="fa fa-remove c-wrong"></i>';
			} else {
				res = '<span class="correct">' + quiz.JS[j].score + '</span><i class="fa fa-check c-correct"></i>';
			}
			
			$("#result").append(
			'<div class="result-question"><span>Q-' + quiz.JS[j].id + '.</span> &nbsp;' + quiz.JS[j].question + '</div>' +
			'<div><b>Correct answer:</b> &nbsp;' + quiz.JS[j].answer + '</div>' +
			'<div class="last-row"><b>Score:</b> &nbsp;' + res +
			
			'</div>' 
			
			);
			
		
			
		}
		
		$("#result").append(`<div class="col-lg-12 text-center"><a href="{{url('kpsc/daily-exam')}}" class="btn btn-info"><i class="fa fa-arrow-left"></i> Back</div>`)
		
	
		//result store 
		var daily_exam = $('#daily_exam').attr('action');
		var token = $('input[name="_token"]').attr('value'); 
		var right = this.score;
        var wrong = this.wrongans;
        var summary = $("#result").html();
        var attempt = $('#attempt').val();
         $('.preloader').show()
		$.ajax({
            url: daily_exam,
            cache: false,
            method : 'POST',
            data: {"_token" : token,"right" : right,"wrong" : wrong,"summary" : summary,"attempt" : attempt},
            success: function(html){
                $('.preloader').hide()
                if(html == 1){
                    console.log('competed')
                }
                else
                {
                     console.log('repeated')
                }
                
            }
        });
		
		
		//end
		
		
		
	}
	
	this.checkAnswer = function(option) {
		var answer = quiz.JS[this.currentque].answer;
		option = option.replace(/\</g,"&lt;")   //for <
		option = option.replace(/\>/g,"&gt;")   //for >
		option = option.replace(/"/g, "&quot;")
	
        if(option == ''){
            	quiz.JS[this.currentque].score = 0;
    			quiz.JS[this.currentque].status = "skipped";
        }
		else if(option ==  quiz.JS[this.currentque].answer) {
			if(quiz.JS[this.currentque].score == "") {
    				quiz.JS[this.currentque].score = 1;
    				quiz.JS[this.currentque].status = "correct";
    		}
		} else {
		    quiz.JS[this.currentque].score = "-0.333";
			quiz.JS[this.currentque].status = "wrong";
		}
		
	}	
	 
	this.changeQuestion = function(cque) {
			this.currentque = this.currentque + cque;
			this.displayQuiz(this.currentque);	
			
	}
	
}


var jsq = new quizApp();

var selectedopt;
	$(document).ready(function() {
			jsq.displayQuiz(0);		
		
	    $('#question-options').on('change', 'input[type=radio][name=option]', function(e) {
			//var radio = $(this).find('input:radio');
			$(this).prop("checked", true);
			
			selectedopt = $(this).val();
		});
		
	});

	
	
	
	$('#next').click(function(e) {
			e.preventDefault();
			$('.preloader').show();
			setTimeout(function(){
			    
    			if(selectedopt) {
    				jsq.checkAnswer(selectedopt);
    			}
    			selectedopt='';
    			jsq.changeQuestion(1);
    				$('.preloader').hide();
    				$('body').scrollTop(0);
			},2000)
	});	
	
	$('#previous').click(function(e) {
		e.preventDefault();
		if(selectedopt) {
			jsq.checkAnswer(selectedopt);
		}
			jsq.changeQuestion(-1);
	});	

         document.addEventListener('contextmenu', function(e) {
           e.preventDefault();
         });


         document.onkeydown = function(e) {
           if(event.keyCode == 123) {
              return false;
           }
           if(e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) {
              return false;
           }
           if(e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) {
              return false;
           }
           if(e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) {
              return false;
           }
           if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) {
              return false;
           }
         }

</script>
@endsection

@extends('layouts.without-bottom')
@extends('kpsc.section_header')
@section('title', $title ?? 'KPSC-Daily Exam List-')
@php $title = "KPSC-Daily Exam List"; @endphp

@section('styles')


    <style>
        .title,
        .subtitle {
            font-family: Montserrat, sans-serif;
            font-weight: normal;
            color: black !important;

        }

        .questionContainer .title,
        .questionContainer .subtitle {
            font-size: 1rem;
            line-height: 1;
            white-space: pre-wrap;
        }


        .animated {
            transition-duration: 0.15s;
        }

        .questionBox {
            height: 100%;
            position: relative;
            display: flex;
        }

        .questionBox .titleContainer {
            background: #29c5dc;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            margin-bottom: 12px;
            margin-top: 20px;
            border-radius: 9px;
            padding: 12px;
        }

        .questionBox .titleContainer h2 {
            color: white;
            padding: 5px;
        }

        .questionBox .quizCompleted {
            width: 100%;
            padding: 25px;
        }

        .questionBox .questionContainer {
            white-space: normal;
            height: 100%;
            width: 100%;
        }

        .questionBox .questionContainer .optionContainer {
            margin-top: 12px;
            flex-grow: 1;
        }

        .questionBox .questionContainer .optionContainer .option {
            border-radius: 290486px;
            padding: 9px 18px;
            margin: 0 18px;
            margin-bottom: 12px;
            transition: 0.3s;
            cursor: pointer;
            border: #726d6dfa 2px solid
        }

        .questionBox .questionContainer .optionContainer .option.is-selected {
            border-color: #209cee;
            background-color: white;
        }

        .questionBox .questionContainer .optionContainer .option:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }

        .questionBox .questionContainer .optionContainer .option:active {
            transform: scaleX(0.9);
        }

        .questionBox .questionContainer .questionFooter {
            width: 100%;
            align-self: flex-end;
        }

        .questionBox .questionContainer .questionFooter .pagination {
            margin: 15px 25px;
            gap: 25px;
        }

        .questionBox .questionContainer .questionFooter .progressContainer {
            color: #fff;
            margin: 10px 15px;
        }

        .pagination a {
            font-size: 13px;
        }

        @media screen and (min-width: 769px) {
            .container {
                height: 100%;
            }

            .container .columns {
                height: 100%;
            }

            .container .columns .column {
                height: 100%;
            }


            .questionBox {
                align-items: center;
                justify-content: center;
            }

            .questionBox .questionContainer {
                display: flex;
                flex-direction: column;
            }
        }
    </style>

@endsection


@section('scripts')
    <script src='https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.17/vue.min.js'></script>
    <script id="rendered-js">
        var quiz = @json($QStn, JSON_PRETTY_PRINT),



            userResponseSkelaton = Array(quiz.questions.length).fill(null);

        var app = new Vue({
            el: "#app",
            data: {
                quiz: quiz,
                questionIndex: 0,
                userResponses: userResponseSkelaton,
                quizStarted: true,
                isActive: false
            },
            filters: {
                charIndex: function(i) {
                    return String.fromCharCode(97 + i);
                }
            },

            methods: {
                selectOption: function(index) {
                    Vue.set(this.userResponses, this.questionIndex, index);
                    //  console.log(this.userResponses);
                },
                next: function() {

                    if (this.questionIndex < this.quiz.questions.length) {
                        this.questionIndex++;

                    }
                },

                prev: function() {
                    if (this.quiz.questions.length > 0) this.questionIndex--;
                },
                // Return "true" count in userResponses
                score: function() {
                    var score = 0;
                    var right = 0;
                    var wrong = 0;
                    var skipped = 0;
                    var jj = 0;
                    var arr = [];

                    var summary = "";
                    var icon;
                    summary += `<div style="border: 1px solid #0000008c;padding: 7px;text-align: left;">`;

                    for (let i = 0; i < this.userResponses.length; i++) {

                        if (typeof this.quiz.questions[i].responses[this.userResponses[i]] !== "undefined" &&
                            this.quiz.questions[i].responses[this.userResponses[i]].correct) {
                            right = right + 1;
                            icon = ` <i class="fa fa-check text-sucess fa-3x"></i>`;
                        } else {
                            if ((typeof this.quiz.questions[i].responses[this.userResponses[i]]) ==
                                "undefined") {
                                skipped = skipped + 1;
                                icon = ``;
                            } else {
                                wrong = wrong + 1;
                                icon = `<i class="fa fa-times text-danger fa-3x"></i>`;
                            }
                        }

                        summary += `<div class="mt-2 border-bottom border-dark">
                                <p>Question:</p>
                                <p>${jj = i+1} . ${this.quiz.questions[i].text}</p>
                                <p>Correct Answer</p>
                                <p>${this.quiz.questions[i].optional}</p>  
                                <p>Your Result</p>
                                <p>${icon}</p>  
                            </div>`;



                    }

                    score = right - (wrong * 0.333);
                    arr[0] = score
                    arr[1] = right
                    arr[2] = wrong
                    arr[3] = skipped
                    arr[4] = summary

                    store_results(score, right, wrong, skipped);

                    return arr;

                    //return this.userResponses.filter(function(val) { return val }).length;
                }
            }
        });


        function store_results(score, right, wrong, skipped) {
            //result store 
            var daily_exam = $('#daily_exam').attr('action');
            var token = $('input[name="_token"]').attr('value');
            var attempt = $('#attempt').val();

            $('.preloader').show();
            jQuery.ajax({
                url: daily_exam,
                cache: false,
                method: 'POST',
                data: {
                    "score": score,
                    "skipped": skipped,
                    "_token": token,
                    "right": right,
                    "wrong": wrong,
                    "attempt": attempt
                },
                success: function(html) {
                    $('.preloader').hide()

                    if (html === 1) {

                        console.log('competed')
                    } else {

                        console.log('repeated')
                    }
                }
            });
        }




        $('body').on('click', '.pagination-previous,.pagination-next', function() {
            $(".preloader").show();
            $('#app').scrollTop(0);
            setTimeout(function() {
                $(".preloader").hide();
            }, 2000);
        });

        setTimeout(function() {
            $(".preloader").hide();
        }, 3000);
    </script>

@endsection


@section('content')

    <div class="preloader" id="preloader">
        <div class="spinner-grow text-secondary" role="status">
            <div class="sr-only">Loading...</div>
        </div>
    </div>

    <!-- Ads -->

    <form action="{{ url('kpsc/daily-exam-store-result/' . $exam_date . '/' . $exam_id) }}" id="daily_exam" method="POST">
        @csrf()
        <input type="hidden" name="attempt" id="attempt" value="{{ $exam_id }}">
    </form>
    <section id="app" class="mt-5">

        <!--heroBody-->
        <div class="mt-5">

            <!--container-->
            <div class="container">

                <!--columns-->
                <div class="columns  box">


                    <!--questionBox-->
                    <div class="column  questionBox ">

                        <!-- transition -->
                        <transition enter-active-class="animated jackInTheBox" leave-active-class="animated zoomOut"
                            mode="out-in">

                            <!--<div class="questionHeader bg-danger text-light">-->
                            <!--    <p class="float-left">Total Questions:</p>-->
                            <!--<p class="float-end">Exam Ended : </p>-->
                            <!--</div>-->

                            <!--qusetionContainer-->
                            <div class="questionContainer" v-if="questionIndex<quiz.questions.length && quizStarted"
                                v-bind:key="questionIndex">

                                <!-- questionTitle -->
                                <div class="titleContainer ">
                                    {{-- <h2 class="title d-inline" v-html="questionIndex+1"></h2> --}}
                                    <h2 class="title d-inline" v-html="quiz.questions[questionIndex].text">

                                    </h2>
                                </div>

                                <!-- quizOptions -->
                                <div class="optionContainer">
                                    {{-- <div class="option" v-for="(response, index) in quiz.questions[questionIndex].responses"
                                        @click="selectOption(index)"
                                        :class="{ 'is-selected': userResponses[questionIndex] == index }"
                                        :key="index">
                                        @{{ index | charIndex }}. @{{ response.text }}
                                    </div> --}}
                                    <div class="option" v-for="(response, index) in quiz.questions[questionIndex].responses"
                                        @click="selectOption(index)"
                                        :class="{ 'is-selected': userResponses[questionIndex] == index }"
                                        :key="index">
                                        <span v-html="String.fromCharCode(97 + index) + '. ' + response.text"></span>
                                    </div>

                                </div>

                                <!--quizFooter: navigation and progress-->
                                <div class="questionFooter">

                                    <!--pagination-->
                                    <nav class="pagination text-center" role="navigation" aria-label="pagination">

                                        <!-- prevButton -->
                                        <a class="pagination-previous btn btn-warning text-light rounded "
                                            v-on:click="prev();" :disabled="questionIndex < 1">
                                            Previous Question
                                        </a>

                                        <!-- prevButton -->
                                        <a class="pagination-next btn btn-info  text-light rounded" v-on:click="next();"
                                            :disabled="questionIndex >= quiz.questions.length">
                                            @{{ (userResponses[questionIndex] == null) ? 'Skip' : 'Next' }} Question
                                        </a>

                                    </nav>
                                    <!--/pagination-->

                                    <!--progress-->
                                    <div class="progressContainer">
                                        <progress class="progress bg-success w-100"
                                            :value="(questionIndex / quiz.questions.length) * 100"
                                            max="100">@{{ (questionIndex / quiz.questions.length) * 100 }}%</progress>
                                    </div>
                                    <!--/progress-->

                                </div>
                                <!--/quizFooter-->

                            </div>
                            <!--/questionContainer-->

                            <!--quizCompletedResult-->
                            <div v-if="questionIndex >= quiz.questions.length && quizStarted" v-bind:key="questionIndex"
                                class="quizCompleted text-center">

                                <!-- quizCompletedIcon: Achievement Icon -->
                                <span class="icon text-success">
                                    <i class="fa fa-check-circle-o fa-3x"></i>
                                </span>

                                <!--resultTitleBlock-->
                                <h2 class="title">
                                    You did completed exam
                                </h2>
                                <p class="subtitle">
                                <table class="table table-bordered  table-striped">
                                    <tr>
                                        <th scop>
                                            Total score:
                                        </th>
                                    </tr>
                                    <tr>
                                        <td>
                                            @{{ score()[0] }} / @{{ quiz.questions.length }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>
                                            Total Right Answers :
                                        </th>
                                    </tr>
                                    <tr>
                                        <td>
                                            @{{ score()[1] }}
                                        </td>
                                    </tr>


                                    <tr>
                                        <th>
                                            Total Wrong Answers :
                                        </th>
                                    </tr>
                                    <tr>
                                        <td>
                                            @{{ score()[2] }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>
                                            Total Skipped Questions :
                                        </th>
                                    </tr>
                                    <tr>
                                        <td>
                                            @{{ score()[3] }}
                                        </td>
                                    </tr>
                                </table>

                                </p>
                                <!--/resultTitleBlock-->
                                <div class="" style="margin-top: 20px;" v-html="score()[4]">

                                </div>
                            </div>
                            <!--/quizCompetedResult-->

                        </transition>

                    </div>
                    <!--/questionBox-->

                </div>
                <!--/columns-->

            </div>
            <!--/container-->

        </div>
        <!--/heroBody-->

    </section>
@endsection


{{-- <div id="app">
    <form id="daily_exam" action="{{ url('kpsc/daily-exam-store-result/' . $exam_date . '/' . $exam_id) }}"
        method="POST">
        @csrf
        <input type="hidden" id="attempt" value="{{ $attempt }}">

        <div v-if="step < quiz.questions.length">
            <h2>Question {{ step + 1 }}: {{ quiz . questions[step] . text }}</h2>
            <div v-for="(response, index) in quiz.questions[step].responses" :key="index">
                <label>
                    <input type="radio" :name="'question_' + step" :value="index"
                        v-model="userResponses[step]">
                    {{ response . text }}
                </label>
            </div>
            <button type="button" @click="next">Next</button>
        </div>

        <div v-if="step === quiz.questions.length">
            <h2>Quiz Complete</h2>
            <button type="button" @click="submitResults">Submit</button>
        </div>
    </form>
</div>

<script>
    new Vue({
        el: "#app",
        data: {
            step: 0,
            userResponses: [],
            quiz: {
                questions: @json($questions) // Ensure the Laravel controller passes questions
            }
        },
        methods: {
            next() {
                if (this.userResponses[this.step] === undefined) {
                    alert("Please select an answer before proceeding.");
                    return;
                }
                this.step++;
            },
            submitResults() {
                let score = 0,
                    right = 0,
                    wrong = 0,
                    skipped = 0;
                let userAnswers = [];

                this.quiz.questions.forEach((question, i) => {
                    let chosenIndex = this.userResponses[i];
                    let chosenAnswer = chosenIndex !== undefined ? question.responses[chosenIndex]
                        .text : "Not Answered";
                    let correctAnswer = question.optional;
                    let isCorrect = chosenIndex !== undefined && question.responses[chosenIndex]
                    .correct;

                    if (isCorrect) right++;
                    else if (chosenIndex === undefined) skipped++;
                    else wrong++;

                    userAnswers.push({
                        question_id: question.id,
                        question_text: question.text,
                        chosen_answer: chosenAnswer,
                        correct_answer: correctAnswer,
                        is_correct: isCorrect
                    });
                });

                score = right - (wrong * 0.333);
                store_results(score, right, wrong, skipped, userAnswers);
            }
        }
    });

    function store_results(score, right, wrong, skipped, userAnswers) {
        let daily_exam = $('#daily_exam').attr('action');
        let token = $('meta[name="csrf-token"]').attr('content');
        let attempt = $('#attempt').val();

        $.ajax({
            url: daily_exam,
            method: 'POST',
            data: {
                "_token": token,
                "score": score,
                "right": right,
                "wrong": wrong,
                "skipped": skipped,
                "attempt": attempt,
                "user_answers": userAnswers
            },
            success: function(response) {
                alert("Exam results saved successfully!");
                console.log(response);
            },
            error: function(error) {
                console.log("Error storing results:", error);
            }
        });
    }
</script> --}}

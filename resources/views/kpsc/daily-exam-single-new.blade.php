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
        border: #726d6dfa 2px solid;
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
<script>
    var quiz = @json($QStn, JSON_PRETTY_PRINT),
        userResponseSkeleton = Array(quiz.questions.length).fill(null);

    var app = new Vue({
        el: "#app",
        data: {
            quiz: quiz,
            questionIndex: 0,
            userResponses: userResponseSkeleton,
            quizStarted: true,
            isActive: false
        },
        methods: {
            selectOption(index) {
                this.$set(this.userResponses, this.questionIndex, index);
            },
            next() {
                if (this.questionIndex < this.quiz.questions.length) {
                    this.questionIndex++;
                }
            },
            prev() {
                if (this.questionIndex > 0) this.questionIndex--;
            },
            score() {
                let score = 0, right = 0, wrong = 0, skipped = 0, summary = "";
                summary += `<div style="border: 1px solid #0000008c;padding: 7px;text-align: left;">`;
                
                this.userResponses.forEach((response, i) => {
                    let correct = this.quiz.questions[i].responses[response]?.correct;
                    if (correct) {
                        right++;
                    } else if (response === null) {
                        skipped++;
                    } else {
                        wrong++;
                    }
                    summary += `<div class="mt-2 border-bottom border-dark">
                                    <p>Question:</p>
                                    <p>${i + 1}. ${this.quiz.questions[i].text}</p>
                                    <p>Correct Answer</p>
                                    <p>${this.quiz.questions[i].optional}</p>
                                </div>`;
                });
                score = right - (wrong * 0.333);
                return [score, right, wrong, skipped, summary];
            }
        }
    });
</script>
@endsection

@section('content')
<div id="app">
    <div v-if="questionIndex < quiz.questions.length">
        <h2 v-html="quiz.questions[questionIndex].text"></h2>
        <div v-for="(response, index) in quiz.questions[questionIndex].responses" @click="selectOption(index)" :class="{ 'is-selected': userResponses[questionIndex] === index }">
            <span v-html="response.text"></span>
        </div>
        <button @click="prev" :disabled="questionIndex === 0">Previous</button>
        <button @click="next" :disabled="questionIndex >= quiz.questions.length">Next</button>
    </div>
    <div v-else>
        <h2>Exam Completed</h2>
        <p>Total Score: {{ score()[0] }}</p>
        <p>Right Answers: {{ score()[1] }}</p>
        <p>Wrong Answers: {{ score()[2] }}</p>
        <p>Skipped Questions: {{ score()[3] }}</p>
        <div v-html="score()[4]"></div>
    </div>
</div>
@endsection

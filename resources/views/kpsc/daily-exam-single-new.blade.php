@extends('layouts.without-bottom')
@extends('kpsc.section_header')
@section('title', $title ?? 'KPSC-Daily Exam List-')
@php $title = "KPSC-Daily Exam List"; @endphp

@section('styles')
    <style>
        .title, .subtitle {
            font-family: Montserrat, sans-serif;
            font-weight: normal;
            color: black !important;
        }

        .questionContainer .title, .questionContainer .subtitle {
            font-size: 1rem;
            line-height: 1;
            white-space: pre-wrap;
        }

        .questionBox {
            height: 100%;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .questionBox .titleContainer {
            background: #29c5dc;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            margin-bottom: 12px;
            margin-top: 20px;
            border-radius: 9px;
            padding: 12px;
            color: white;
            text-align: center;
        }

        .optionContainer .option {
            border-radius: 25px;
            padding: 10px 20px;
            margin: 10px;
            transition: 0.3s;
            cursor: pointer;
            border: #726d6dfa 2px solid;
        }

        .optionContainer .option:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }
    </style>
@endsection

@section('content')
    <div class="container mt-5">
        <form action="{{ url('kpsc/daily-exam-store-result/' . $exam_date . '/' . $exam_id) }}" id="daily_exam" method="POST">
            @csrf()
            <input type="hidden" name="attempt" id="attempt" value="{{ $exam_id }}">
        </form>

        <div class="questionBox1 mt-5">
            @foreach($QStn['questions'] as $index => $question)
                <div class="questionContainer" id="question-{{ $index }}" style="display: {{ $index == 0 ? 'block' : 'none' }};">
                    <div class="titleContainer">
                        <h2 class="title">
                            {{-- {{ $index + 1 }}. --}}
                             {!! $question['text'] !!}</h2>
                    </div>

                    <div class="optionContainer">
                        @foreach($question['responses'] as $respIndex => $response)
                            <div class="option" onclick="selectOption({{ $index }}, {{ $respIndex }})">
                                {!! chr(97 + $respIndex) !!}. {!! $response['text'] !!}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="navigation mt-3">
                <button id="prevBtn" class="btn btn-warning" onclick="prevQuestion()" disabled>Previous</button>
                <button id="nextBtn" class="btn btn-info" onclick="nextQuestion()">Next</button>
            </div>
        </div>
    </div>

    <script>
        let currentQuestion = 0;
        const totalQuestions = {{ count($QStn['questions']) }};

        function selectOption(qIndex, optionIndex) {
            document.getElementById('question-' + qIndex).dataset.selected = optionIndex;
        }

        function showQuestion(index) {
            document.querySelectorAll('.questionContainer').forEach((q, i) => {
                q.style.display = (i === index) ? 'block' : 'none';
            });
            document.getElementById('prevBtn').disabled = index === 0;
            document.getElementById('nextBtn').innerText = index === totalQuestions - 1 ? 'Submit' : 'Next';
        }

        function nextQuestion() {
            if (currentQuestion < totalQuestions - 1) {
                currentQuestion++;
                showQuestion(currentQuestion);
            } else {
                document.getElementById('daily_exam').submit();
            }
        }

        function prevQuestion() {
            if (currentQuestion > 0) {
                currentQuestion--;
                showQuestion(currentQuestion);
            }
        }
    </script>
@endsection

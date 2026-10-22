<?php

return [

    /*
     | Languages a question can be written in. The code is stored with every text.
     | "aliases" are the names people type in the Language column of a file.
     */
    'languages' => [
        'en' => ['name' => 'English', 'aliases' => ['en', 'eng', 'english']],
        'ml' => ['name' => 'Malayalam', 'aliases' => ['ml', 'mal', 'malayalam', 'മലയാളം']],
        'ta' => ['name' => 'Tamil', 'aliases' => ['ta', 'tam', 'tamil']],
        'hi' => ['name' => 'Hindi', 'aliases' => ['hi', 'hin', 'hindi']],
        'kn' => ['name' => 'Kannada', 'aliases' => ['kn', 'kan', 'kannada']],
    ],

    /*
     | The kinds of file that can be imported.
     |   single  = one language per question          multi = one question written in several languages
     |   images  = pictures in the question / options are expected
     */
    'types' => [
        'english' => ['label' => 'English only', 'hint' => 'Questions in English (single language).', 'single' => true, 'language' => 'en'],
        'native' => ['label' => 'Native language', 'hint' => 'Questions in one native language, e.g. Malayalam (single language).', 'single' => true, 'language' => null],
        'image' => ['label' => 'Image with content', 'hint' => 'Questions or answer options that contain pictures: maths, figures, diagrams.', 'single' => true, 'language' => null],
        'multi' => ['label' => 'One question, many languages', 'hint' => 'The same question written in several languages (e.g. English + Malayalam).', 'single' => false, 'language' => null],
    ],

    /*
     | Column names understood in Word tables, CSV and Excel files (header row, any case, spaces and _ ignored).
     */
    'headers' => [
        'no' => ['no', 'number', 'qno', 'qn', 'slno', 'sno', 'sl', 'serial', 'questionno', 'questionnumber'],
        'language' => ['language', 'lang', 'ഭാഷ'],
        'question' => ['question', 'q', 'questiontext', 'ചോദ്യം'],
        'option_1' => ['optiona', 'a', 'option1', 'opt1', 'opta', 'choicea', 'ഓപ്ഷൻa'],
        'option_2' => ['optionb', 'b', 'option2', 'opt2', 'optb', 'choiceb', 'ഓപ്ഷൻb'],
        'option_3' => ['optionc', 'c', 'option3', 'opt3', 'optc', 'choicec', 'ഓപ്ഷൻc'],
        'option_4' => ['optiond', 'd', 'option4', 'opt4', 'optd', 'choiced', 'ഓപ്ഷൻd'],
        'answer' => ['answer', 'ans', 'correct', 'correctanswer', 'correctoption', 'right', 'ഉത്തരം'],
        'explanation' => ['explanation', 'exp', 'solution', 'reason', 'notes', 'വിശദീകരണം'],
        'difficulty' => ['difficulty', 'level'],
        // pictures in CSV / Excel: the file name of a picture inside the uploaded images zip
        'question_image' => ['questionimage', 'qimage', 'image', 'questionpicture', 'picture'],
        'option_1_image' => ['optionaimage', 'aimage', 'option1image', 'imagea', 'image1'],
        'option_2_image' => ['optionbimage', 'bimage', 'option2image', 'imageb', 'image2'],
        'option_3_image' => ['optioncimage', 'cimage', 'option3image', 'imagec', 'image3'],
        'option_4_image' => ['optiondimage', 'dimage', 'option4image', 'imaged', 'image4'],
        'explanation_image' => ['explanationimage', 'eimage', 'solutionimage'],
    ],

    'max_file_kb' => 20480,        // 20 MB for the Word / CSV / Excel file
    'max_zip_kb' => 51200,         // 50 MB for the pictures zip
    'max_image_bytes' => 3145728,  // 3 MB per picture
    'max_questions_per_file' => 2000,
];

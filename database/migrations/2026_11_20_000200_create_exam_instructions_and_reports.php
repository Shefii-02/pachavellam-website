<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Instruction templates written once and picked per exam (OMR / MCQ), and the "report an error" box of the exam pages. */
return new class extends Migration {
    public function up()
    {
        Schema::create('exam_instructions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('kind', 10);                  // omr | mcq
            $table->longText('body');                    // html
            $table->boolean('is_default')->default(false);   // used by the exams of this kind that pick none
            $table->timestamps();
        });

        Schema::table('kpsc_daily_exams', function (Blueprint $table) {
            $table->unsignedBigInteger('instruction_id')->nullable()->after('cutoff_mark');
        });

        Schema::create('exam_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('exam_id')->index();
            $table->string('question_no', 20)->nullable();
            $table->string('type', 40);
            $table->text('message');
            $table->string('status', 10)->default('open');   // open | resolved
            $table->timestamps();
        });

        $now = now();
        DB::table('exam_instructions')->insert([
            ['name' => 'OMR exam (Malayalam)', 'kind' => 'omr', 'is_default' => true, 'created_at' => $now, 'updated_at' => $now, 'body' => '<ul>'
                . '<li>✅ പരീക്ഷയുടെ ദൈർഘ്യം ഒരു മണിക്കൂർ 15 മിനിറ്റ്</li>'
                . '<li>✅ ഓരോ ശരിയുത്തരങ്ങൾക്കും ഓരോ മാർക്ക് വീതം</li>'
                . '<li>✅ ഉത്തരം തെറ്റിയാൽ 1/3 നെഗറ്റീവ് മാർക്ക് ഉണ്ടായിരിക്കുന്നതാണ്</li>'
                . '<li>✅ അറ്റൻഡ് ചെയ്യാത്ത ചോദ്യങ്ങൾക്ക് മാർക്ക് നഷ്ടപ്പെടുന്നതല്ല</li>'
                . '<li>✅ ഇംഗ്ലീഷ് മലയാളം മാത്തമാറ്റിക് ജികെ എന്നീ വിഭാഗങ്ങളുടെ മാർക്ക് എടുത്ത് OMR ഷീറ്റിൽ എഴുതേണ്ടതാണ്</li>'
                . '<li>✅ പരീക്ഷയെഴുതിയ ശേഷം നിങ്ങൾക്ക് ലഭിച്ച ആകെ ശരിയുത്തരങ്ങൾ, എത്ര ചോദ്യമാണ് തെറ്റിയത് എന്നിവ ഇവിടെ അപ്‌ലോഡ് ചെയ്യുക</li>'
                . '<li>✅ നിശ്ചിത സമയത്തിനുള്ളിൽ ലീഡർ ബോർഡ് നിങ്ങൾക്ക് ലഭ്യമാകുന്നതാണ്</li></ul>'],
            ['name' => 'MCQ exam (English)', 'kind' => 'mcq', 'is_default' => true, 'created_at' => $now, 'updated_at' => $now, 'body' => '<h6>General instructions</h6><ol>'
                . '<li>There is only one correct answer to each question. Click on the most appropriate option to mark it as your answer.</li>'
                . '<li>You can change your answer by clicking on another option.</li>'
                . '<li>You are awarded 1 mark for each correct answer. There is a 1/3 penalty for each wrong answer. Unanswered questions carry no penalty.</li>'
                . '<li>Do not use calculators, dictionaries or any other printed/online reference material during the test.</li>'
                . '<li>Do not submit the test before completing it. A submitted test cannot be resumed.</li>'
                . '<li>You can attempt this exam again; every attempt is saved, and the leaderboard counts your first attempt.</li></ol>'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('exam_reports');
        Schema::table('kpsc_daily_exams', function (Blueprint $table) {
            $table->dropColumn('instruction_id');
        });
        Schema::dropIfExists('exam_instructions');
    }
};

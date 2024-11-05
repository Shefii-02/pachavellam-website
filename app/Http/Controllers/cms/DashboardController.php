<?php

namespace App\Http\Controllers\cms;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\Notification;
use App\Models\WhatsNew;
use App\Models\SpecialTab;
use App\Models\Ourvideos;
use App\Models\CategoryPaid;
use App\Models\CategoryFree;
use App\Models\SpecialVideo;
use App\Models\Bulletin;
use App\Models\SubcategoryFree;
use App\Models\ClassFree;
use App\Models\ClassPaid;
use App\Models\SubcategoryPaid;
use App\Models\NewmodalQa;
use App\Models\Syllabus;
use App\Models\Material;
use App\Models\PrevQuestion;
use App\Models\Pscresults;
use App\Models\CurrentAffairs;
use App\Models\Pscnews;
use App\Models\Capsule;
use App\Models\CapsuleComment;
use App\Models\CapsuleLike;
use App\Models\Timetable;
use App\Models\DailyBucket;
use App\Models\DailyExam;
use App\Models\DailyExamdetails;
use App\Models\User;
use App\Models\ExamDiscussion;
use Storage;
use Hash;
use Illuminate\Support\Str;
use App\Models\DailyExamattempt;
use Validator;

class DashboardController extends Controller
{
    //
    public function dashboard(){
        
        
        $student_count = User::where('type','Student')->count();
        $Staff_count = User::where('type','Admin Staff')->count();
        $teacher_count = User::where('type','Teacher')->count();
        
        
        
        return view('cms.index',compact('student_count','Staff_count','teacher_count'));
    }
}
<?php

namespace App\Http\Controllers\ssc;

use App\Http\Controllers\Concerns\ManagesExamSections;
use App\Http\Controllers\Controller;

class SectionController extends Controller
{
    use ManagesExamSections;

    protected $moduleSlug = 'ssc';
    protected $moduleLabel = 'SSC';
    protected $moduleFolder = 'ssc';
}

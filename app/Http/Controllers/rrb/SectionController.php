<?php

namespace App\Http\Controllers\rrb;

use App\Http\Controllers\Concerns\ManagesExamSections;
use App\Http\Controllers\Controller;

class SectionController extends Controller
{
    use ManagesExamSections;

    protected $moduleSlug = 'rrb';
    protected $moduleLabel = 'RRB';
    protected $moduleFolder = 'rrb';
}

<?php

namespace App\Http\Controllers\ktet;

use App\Http\Controllers\Concerns\ManagesExamSections;
use App\Http\Controllers\Controller;

class SectionController extends Controller
{
    use ManagesExamSections;

    protected $moduleSlug = 'ktet';
    protected $moduleLabel = 'KTET';
    protected $moduleFolder = 'ktet';
}

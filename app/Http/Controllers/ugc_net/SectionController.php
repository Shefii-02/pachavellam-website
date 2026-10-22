<?php

namespace App\Http\Controllers\ugc_net;

use App\Http\Controllers\Concerns\ManagesExamSections;
use App\Http\Controllers\Controller;

class SectionController extends Controller
{
    use ManagesExamSections;

    protected $moduleSlug = 'ugc-net';
    protected $moduleLabel = 'UGC NET';
    protected $moduleFolder = 'ugc_net';
}

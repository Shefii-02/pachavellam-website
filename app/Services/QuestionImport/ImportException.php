<?php

namespace App\Services\QuestionImport;

/** The file cannot be imported at all; the message is safe to show to the person uploading. */
class ImportException extends \RuntimeException
{
}

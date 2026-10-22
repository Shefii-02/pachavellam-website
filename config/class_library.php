<?php

return [

    /*
     | Storage the class files live on. "disk" is a disk of config/filesystems.php: set CLASS_STORAGE_DISK=s3 (and the
     | AWS_* values) when the classes are kept in an S3 bucket - the S3 driver needs the package
     | league/flysystem-aws-s3-v3 (composer require league/flysystem-aws-s3-v3).
     | "root" is the folder inside the bucket / disk where class folders are kept; "extensions" are the files the
     | storage browser shows as classes.
     */
    'storage' => [
        'disk' => env('CLASS_STORAGE_DISK'),
        'root' => env('CLASS_STORAGE_ROOT', ''),
        'extensions' => ['mp4', 'm4v', 'mov', 'webm', 'mkv', 'm3u8', 'mp3', 'm4a'],
        'link_minutes' => 360,       // how long a private file's play address stays valid
        'max_listing' => 400,        // files shown per folder in the browser
    ],

    /* Extra host names (a CDN domain such as cdn.example.com) that are treated as CDN links. */
    'cdn_hosts' => array_filter(explode(',', (string) env('CLASS_CDN_HOSTS', ''))),

    'sources' => [
        'youtube' => 'YouTube link (unlisted)',
        'cdn' => 'CDN / video file link',
        's3' => 'Cloud storage file (S3 ...)',
        'link' => 'Other link',
    ],

    'statuses' => [
        'draft' => ['Draft', 'secondary'],
        'published' => ['Published', 'success'],
        'archived' => ['Archived', 'dark'],
    ],
];

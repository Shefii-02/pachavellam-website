<?php

/*
| Media library settings.
| 'specs' are the sizes shown next to every picture field ("Recommended 1280 x 720 px, 16:9") and used to tell the
| staff if the chosen picture fits. They are advice only - any picture can be used. Change them here when a design changes.
*/
return [
    'disk' => 'public',
    'root' => 'files/media',            // new uploads: storage/app/public/files/media/YYYY/MM/...
    'max_mb' => 64,                     // largest single upload (the server's upload_max_filesize / post_max_size must allow it too)

    'types' => [
        'image' => ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'],
        'video' => ['mp4', 'webm', 'mov', 'm4v', 'mkv'],
        'audio' => ['mp3', 'wav', 'm4a', 'aac', 'ogg', 'opus', 'amr'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'zip', 'rar', 'epub'],
    ],

    'specs' => [
        'banner' => ['label' => 'Banner', 'w' => 1280, 'h' => 720, 'ratio' => '16:9', 'max_mb' => 2, 'formats' => 'JPG, PNG or WEBP'],
        'story' => ['label' => 'Story', 'w' => 1080, 'h' => 1920, 'ratio' => '9:16', 'max_mb' => 3, 'formats' => 'JPG, PNG or WEBP'],
        'cover' => ['label' => 'Cover picture', 'w' => 1280, 'h' => 720, 'ratio' => '16:9', 'max_mb' => 2, 'formats' => 'JPG, PNG or WEBP'],
        'square' => ['label' => 'Square picture', 'w' => 600, 'h' => 600, 'ratio' => '1:1', 'max_mb' => 1, 'formats' => 'JPG, PNG or WEBP'],
        'icon' => ['label' => 'Icon', 'w' => 256, 'h' => 256, 'ratio' => '1:1', 'max_mb' => 0.5, 'formats' => 'PNG or WEBP (transparent is fine)'],
        'logo' => ['label' => 'Logo', 'w' => 400, 'h' => 200, 'ratio' => '2:1', 'max_mb' => 0.5, 'formats' => 'PNG or SVG with a transparent background'],
        'share' => ['label' => 'Share picture', 'w' => 1200, 'h' => 630, 'ratio' => '1.91:1', 'max_mb' => 2, 'formats' => 'JPG or PNG'],
        'image' => ['label' => 'Picture', 'w' => 1280, 'h' => 720, 'ratio' => null, 'max_mb' => 2, 'formats' => 'JPG, PNG or WEBP'],
        'file' => ['label' => 'File', 'w' => null, 'h' => null, 'ratio' => null, 'max_mb' => 10, 'formats' => 'PDF, Word, Excel, PowerPoint or image'],
        'audio' => ['label' => 'Voice / audio', 'w' => null, 'h' => null, 'ratio' => null, 'max_mb' => 20, 'formats' => 'MP3, M4A or WAV'],
        'video' => ['label' => 'Video', 'w' => 1280, 'h' => 720, 'ratio' => '16:9', 'max_mb' => 64, 'formats' => 'MP4 (H.264)'],
    ],
];

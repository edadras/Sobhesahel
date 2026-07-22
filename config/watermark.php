<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Watermark image
    |--------------------------------------------------------------------------
    | Path to the watermark image (PNG with transparency is recommended).
    | A relative path is resolved against the public/ directory; an absolute
    | filesystem path is also accepted.
    */
    'image' => env('WATERMARK_IMAGE', 'asset/img/logo.png'),

    /*
    |--------------------------------------------------------------------------
    | Opacity (0 - 100)
    |--------------------------------------------------------------------------
    | 100 = fully opaque, 0 = invisible. Applied on top of the watermark's
    | own alpha channel.
    */
    'opacity' => env('WATERMARK_OPACITY', 55),

    /*
    |--------------------------------------------------------------------------
    | Position
    |--------------------------------------------------------------------------
    | One of: top-left, top-right, bottom-left, bottom-right, center
    */
    'position' => env('WATERMARK_POSITION', 'bottom-right'),

    /*
    |--------------------------------------------------------------------------
    | Scale
    |--------------------------------------------------------------------------
    | Watermark width as a fraction of the target image width (0 - 1).
    */
    'scale' => env('WATERMARK_SCALE', 0.18),

    /*
    |--------------------------------------------------------------------------
    | Padding (px)
    |--------------------------------------------------------------------------
    | Distance from the selected corner, in pixels.
    */
    'padding' => env('WATERMARK_PADDING', 24),

    /*
    |--------------------------------------------------------------------------
    | Minimum image width (px)
    |--------------------------------------------------------------------------
    | Images narrower than this are left untouched (a watermark would be
    | unreadable / too intrusive on tiny images).
    */
    'min_width' => env('WATERMARK_MIN_WIDTH', 200),

    /*
    |--------------------------------------------------------------------------
    | Originals directory
    |--------------------------------------------------------------------------
    | Directory (on the same disk as the upload) where the pristine, un-
    | watermarked copy of each file is stored before the watermark is applied.
    */
    'originals_dir' => env('WATERMARK_ORIGINALS_DIR', 'watermark-originals'),

    /*
    |--------------------------------------------------------------------------
    | Output quality
    |--------------------------------------------------------------------------
    | Quality used when re-encoding JPEG / WebP output (0 - 100).
    */
    'quality' => env('WATERMARK_QUALITY', 90),

];

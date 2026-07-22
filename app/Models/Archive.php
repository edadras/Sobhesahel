<?php

namespace App\Models;

use App\Traits\ContentTrait;
use App\Traits\HasTitleValues;
use App\Traits\ImageOptimizer;
use Illuminate\Database\Eloquent\Model;

class Archive extends Model
{
    use ContentTrait,HasTitleValues,ImageOptimizer;

    protected $guarded = ['id'];

    public function archive_category()
    {
        return $this->belongsTo(ArchiveCategory::class,'category_id');
    }
}

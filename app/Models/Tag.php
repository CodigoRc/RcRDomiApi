<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'tags';

    protected $fillable = [
        'id',
        'title',
    ];

    /**
     * The tasks that belong to the tag.
     */
    // public function tasks()
    // {
    //     return $this->belongsToMany('App\Models\TaskX', 'taskx_tags', 'tag_id', 'taskx_id');
    // }
}
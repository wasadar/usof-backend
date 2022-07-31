<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'author',
        /*'message_type',*/
        'post_id',
        'comment_id'
    ];
}

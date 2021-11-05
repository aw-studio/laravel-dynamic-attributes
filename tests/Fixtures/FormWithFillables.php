<?php

namespace Tests\Fixtures;

use AwStudio\DynamicAttributes\HasDynamicAttributes;
use Illuminate\Database\Eloquent\Model;

class FormWithFillables extends Model
{
    use HasDynamicAttributes;

    public $table = 'forms';

    protected $fillable = [
        'title'
    ];
}

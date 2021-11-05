<?php

namespace Tests\Fixtures;

use AwStudio\DynamicAttributes\HasDynamicAttributes;
use Illuminate\Database\Eloquent\Model;

class FormWithGuarded extends Model
{
    use HasDynamicAttributes;

    public $table = 'forms';

    protected $guarded = [
        'password'
    ];
}

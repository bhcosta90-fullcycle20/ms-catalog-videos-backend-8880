<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Abstracts\BasicCrudController
{
    protected function model()
    {
        return new Video;
    }

    protected function ruleStore()
    {
        return [];
    }

    protected function rulePut()
    {
        return [];
    }
}

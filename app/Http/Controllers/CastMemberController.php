<?php

namespace App\Http\Controllers;

use App\Models\CastMember;

class CastMemberController extends Abstracts\BasicCrudController
{
    protected function model()
    {
        return new CastMember();
    }

    protected function ruleStore()
    {
        return [
            'name' => 'required|min:3|max:100',
            'is_active' => 'nullable|boolean',
            'type' => "required|in:" . implode(',', CastMember::TYPES),
        ];
    }

    protected function rulePut()
    {
        return $this->ruleStore();
    }
}

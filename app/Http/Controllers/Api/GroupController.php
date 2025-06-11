<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends CoreController
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function createGroup(Request $request)
    {
        $data = $request->all();
        $group = Group::create([
            'title' => $data['title']
        ]);
        $group->users()->sync($data['users']);

        return $this->responseSuccess(['group' => $group]);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function updateGroup(Request $request)
    {
        $data = $request->all();
        $group = Group::find($data['id']);
        $group->users()->sync($data['users']);
        $group->update([
            'title' => $data['title']
        ]);

        return $this->responseSuccess(['group' => $group]);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function deleteGroup(Request $request)
    {
        $data = $request->all();
        $group = Group::find($data['id']);
        $group->users()->detach();

        return $this->responseSuccess(['message' => 'Группа успешно удалена']);
    }
}


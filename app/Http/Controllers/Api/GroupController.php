<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use App\Http\Resources\Group\GroupsResource;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends CoreController
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function getGroups(Request $request)
    {
        $data = $request->all();
        $builder = Group::query();

        if (isset($data['q'])) {
            $builder->where('title', 'LIKE', '%' . $data['q'] . '%');
        }

        $this->setSorting($builder, [
            'id' => 'id',
            'title' => 'title',
            'created_at' => 'created_at'
        ]);

        $groups = $builder->paginate($data['perPage'] ?? 15);

        return $this->responseSuccess(new GroupsResource($groups));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function createGroup(Request $request)
    {
        $data = $request->all();
        $group = Group::create([
            'title' => $data['title'],
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
            'title' => $data['title'],
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
        $group->delete();

        return $this->responseSuccess(['message' => 'Группа успешно удалена']);
    }
}


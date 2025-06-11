<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use App\Http\Resources\Project\ProjectResource;
use App\Http\Resources\Project\ProjectsResource;
use App\Models\Group;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectController extends CoreController
{
    public function getProjects(Request $request)
    {
        $data = $request->all();
        $projects = Project::orderBy('id', 'desc')
            ->paginate($data['perPage'] ?? 15);

        return $this->responseSuccess(new ProjectsResource($projects));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function createProject(Request $request)
    {
        $data = $request->all();
        $project = Project::create([
            'title' => $data['title']
        ]);

        if (isset($data['users'])) {
            $project->users()->sync($data['users']);
        }

        if (isset($data['groups'])) {
            $project->groups()->sync($data['groups']);
        }

        return $this->responseSuccess(new ProjectResource($project));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function updateProject(Request $request)
    {
        $data = $request->all();
        $project = Project::find($data['id']);

        if (isset($data['users'])) {
            $project->users()->sync($data['users']);
        }

        if (isset($data['groups'])) {
            $project->groups()->sync($data['groups']);
        }

        $project->update([
            'title' => $data['title']
        ]);

        return $this->responseSuccess(new ProjectResource($project));
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function deleteProject(Request $request)
    {
        $data = $request->all();
        $project = Project::find($data['id']);
        $project->users()->detach();
        $project->groups()->detach();

        return $this->responseSuccess(['message' => 'Проект успешно удален']);
    }
}

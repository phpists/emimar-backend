<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use App\Http\Requests\Project\DeleteProjectRequest;
use App\Http\Resources\Project\ProjectResource;
use App\Http\Resources\Project\ProjectsResource;
use App\Models\FileEntry;
use App\Models\Group;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectController extends CoreController
{
    public function getProjects(Request $request)
    {
        $data = $request->all();
        $query = Project::query();

        if (isset($data['q'])) {
            $query->where('title', 'LIKE', '%' . $data['q'] . '%');
        }

        $query->orderBy('id', 'desc');
        $projects = $query->paginate($data['perPage'] ?? 15);

        return $this->responseSuccess([
            'projects' => new ProjectsResource($projects),
            'count' => $projects->total()
        ]);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function createProject(Request $request)
    {
        $data = $request->all();
        $project = Project::create([
            'title' => $data['title'],
            'rules_type' => $data['rules_type'],
        ]);

        if (isset($data['users'])) {
            $project->users()->sync($data['users']);
        }

        if (isset($data['groups'])) {
            $project->groups()->sync($data['groups']);
        }

        if ($project) {
            FileEntry::createProjectStructure($project->id);
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
            'title' => $data['title'],
            'rules_type' => $data['rules_type'],
        ]);

        return $this->responseSuccess(new ProjectResource($project));
    }

    /**
     * @param DeleteProjectRequest $request
     * @return mixed
     */
    public function deleteProject(DeleteProjectRequest $request)
    {
        $data = $request->all();
        $project = Project::find($data['id']);
        $project->users()->detach();
        $project->groups()->detach();
        $project->delete();

        return $this->responseSuccess(['message' => 'Проект успешно удален']);
    }
}

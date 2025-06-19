<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use App\Http\Requests\Project\DeleteProjectRequest;
use App\Http\Resources\Project\ProjectResource;
use App\Http\Resources\Project\ProjectsResource;
use App\Models\FileEntry;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends CoreController
{
    public function getProjects(Request $request)
    {
        $data = $request->all();
        $user = auth()->user();

        $builder = Project::query();

        if ($user->role_id == User::CUSTOMER) {
            $userId = auth()->id();
            $groupIds = DB::table('groups_users')
                ->where('user_id', $userId)
                ->pluck('group_id')
                ->toArray();

            $builder->where(function ($query) use ($userId, $groupIds) {
                $query->where(function ($q) use ($userId) {
                    $q->where('rules_type', 'users')
                        ->whereIn('id', function ($sub) use ($userId) {
                            $sub->select('project_id')
                                ->from('projects_users')
                                ->where('user_id', $userId);
                        });
                });

                if (!empty($groupIds)) {
                    $query->orWhere(function ($q) use ($groupIds) {
                        $q->where('rules_type', 'groups')
                            ->whereIn('id', function ($sub) use ($groupIds) {
                                $sub->select('project_id')
                                    ->from('groups_projects')
                                    ->whereIn('group_id', $groupIds);
                            });
                    });
                }
            });
        }

        if (isset($data['q'])) {
            $builder->where('title', 'LIKE', '%' . $data['q'] . '%');
        }

        $this->setSorting($builder, [
            'id' => 'id',
            'title' => 'title',
        ]);

        $projects = $builder->paginate($data['perPage'] ?? 15);

        return $this->responseSuccess([
            'projects' => new ProjectsResource($projects, false),
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
        $user = auth()->user();

        if ($user->role_id == User::CUSTOMER){
            return $this->responseSuccess([
                'message' => 'Запрещенные права доступа на создание проектов'
            ]);
        }

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
        $user = auth()->user();

        if ($user->role_id == User::CUSTOMER){
            return $this->responseSuccess([
                'message' => 'Запрещенные права доступа на редактирование проектов'
            ]);
        }

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
        $user = auth()->user();

        if ($user->role_id == User::CUSTOMER){
            return $this->responseSuccess([
                'message' => 'Запрещенные права доступа на удаление проектов'
            ]);
        }

        $project = Project::find($data['id']);
        $project->users()->detach();
        $project->groups()->detach();
        $project->delete();

        return $this->responseSuccess(['message' => 'Проект успешно удален']);
    }
}

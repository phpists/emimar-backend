<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use App\Models\FileEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileEntryController extends CoreController
{
    public function getProjectTree(Request $request)
    {
        $data = $request->all();
        $projectId = $data['project_id'];
        $tree = $this->buildTree(null, $projectId);

        return response()->json($tree);
    }

    /**
     * Створити директорію
     * @param Request $request
     * @return mixed
     */
    public function createFolder(Request $request)
    {
        $data = $request->all();

        $projectId = $data['project_id'];
        $folderName = $data['folder_name'];
        $parentId = $data['parent_id'] ?? null;

        if ($parentId) {
            $parentFolder = FileEntry::findOrFail($parentId);
            $path = $parentFolder->path . '/' . $folderName;
        } else {
            $path = "projects/$projectId/$folderName";
        }

        Storage::makeDirectory($path);

        $folder = FileEntry::create([
            'name' => $folderName,
            'type' => 'folder',
            'parent_id' => $parentId,
            'project_id' => $projectId,
            'path' => $path,
        ]);

        return $this->responseSuccess(['folder' => $folder]);
    }

    /**
     * Редагування папки
     * @param Request $request
     * @return mixed
     */
    public function updateFolder(Request $request)
    {
        $data = $request->validate([
            'folder_id' => 'required|integer|exists:file_entries,id',
            'new_name' => 'required|string|max:255',
        ]);

        $folder = FileEntry::findOrFail($data['folder_id']);

        $oldPath = $folder->path;
        $newName = $data['new_name'];
        $parentPath = dirname($oldPath);
        $newPath = $parentPath === '.' ? $newName : $parentPath . '/' . $newName;

        if (Storage::exists($oldPath)) {
            Storage::move($oldPath, $newPath);
        }

        $folder->name = $newName;
        $folder->path = $newPath;
        $folder->save();

        $this->updateChildrenPaths($folder->id, $oldPath, $newPath);

        return $this->responseSuccess(['folder' => $folder]);
    }

    /**
     * Видалити папку
     * @return mixed
     */
    public function deleteFolder(Request $request)
    {
        $data = $request->validate([
            'folder_id' => 'required|integer|exists:file_entries,id',
        ]);

        $folder = FileEntry::findOrFail($data['folder_id']);

        if (Storage::exists($folder->path)) {
            Storage::deleteDirectory($folder->path);
        }

        $this->deleteChildrenRecursive($folder->id);
        $folder->delete();

        return response()->json(['message' => 'Папка успешно удалена']);
    }

    protected function updateChildrenPaths($parentId, $oldBasePath, $newBasePath)
    {
        $children = FileEntry::where('parent_id', $parentId)->get();

        foreach ($children as $child) {
            $oldPath = $child->path;
            $newPath = preg_replace('/^' . preg_quote($oldBasePath, '/') . '/', $newBasePath, $oldPath);

            if (Storage::exists($oldPath)) {
                Storage::move($oldPath, $newPath);
            }

            $child->path = $newPath;
            $child->save();

            $this->updateChildrenPaths($child->id, $oldPath, $newPath);
        }
    }

    /**
     * Переміщення папки
     * @param Request $request
     * @return mixed
     */
    public function moveFolder(Request $request)
    {
        $data = $request->validate([
            'folder_id' => 'required|integer|exists:file_entries,id',
            'new_parent_id' => 'nullable|integer|exists:file_entries,id',
        ]);

        $folder = FileEntry::findOrFail($data['folder_id']);
        $newParentId = $data['new_parent_id'];

        $oldPath = $folder->path;

        if ($newParentId) {
            $newParent = FileEntry::findOrFail($newParentId);
            $newPath = $newParent->path . '/' . $folder->name;
        } else {
            $newPath = "projects/{$folder->project_id}/{$folder->name}";
        }

        if (Storage::exists($oldPath)) {
            Storage::move($oldPath, $newPath);
        }

        $folder->parent_id = $newParentId;
        $folder->path = $newPath;
        $folder->save();

        $this->moveUpdateChildrenPaths($folder->id, $oldPath, $newPath);

        return response()->json(['message' => 'Папка перемещена успешно']);
    }

    protected function deleteChildrenRecursive($parentId)
    {
        $children = FileEntry::where('parent_id', $parentId)->get();

        foreach ($children as $child) {
            if ($child->type === 'folder') {
                $this->deleteChildrenRecursive($child->id);
                if (Storage::exists($child->path)) {
                    Storage::deleteDirectory($child->path);
                }
            } else {
                if (Storage::exists($child->path)) {
                    Storage::delete($child->path);
                }
            }

            $child->delete();
        }
    }

    protected function moveUpdateChildrenPaths($parentId, $oldBasePath, $newBasePath)
    {
        $children = FileEntry::where('parent_id', $parentId)->get();

        foreach ($children as $child) {
            $oldPath = $child->path;
            $newPath = preg_replace('/^' . preg_quote($oldBasePath, '/') . '/', $newBasePath, $oldPath);

            if ($child->type === 'folder') {
                if (Storage::exists($oldPath)) {
                    Storage::move($oldPath, $newPath);
                }
            } else {
                if (Storage::exists($oldPath)) {
                    Storage::move($oldPath, $newPath);
                }
            }

            $child->path = $newPath;
            $child->save();

            if ($child->type === 'folder') {
                $this->moveUpdateChildrenPaths($child->id, $oldPath, $newPath);
            }
        }
    }

    protected function buildTree($parentId, $projectId)
    {
        $folders = FileEntry::where('parent_id', $parentId)
            ->where('project_id', $projectId)
            ->where('type', 'folder')
            ->orderBy('name')
            ->get();

        $tree = [];

        foreach ($folders as $folder) {
            $tree[] = [
                'id' => $folder->id,
                'name' => $folder->name,
                'path' => $folder->path,
                'type' => $folder->type,
                'children' => $this->buildTree($folder->id, $projectId),
            ];
        }

        return $tree;
    }
}

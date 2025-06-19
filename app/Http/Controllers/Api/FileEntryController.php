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

        return $this->responseSuccess(['tree' => $tree]);
    }

    public function getProjectFileEntry(Request $request)
    {
        $data = $request->all();
        $q = isset($data['q']) ? $data['q'] : '';
        $projectId = $data['project_id'];
        $parentId = isset($data['parent_id']) ? $data['parent_id'] : '';
        $list = $this->buildProjectList($projectId, $parentId, $q);

        return $this->responseSuccess(['list' => $list]);
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
            'full_name' => ucfirst($folderName),
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
        $folder->full_name = ucfirst($newName);
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

        return $this->responseSuccess(['message' => 'Папка успешно удалена']);
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

        return $this->responseSuccess(['message' => 'Папка перемещена успешно']);
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
                'full_name' => $folder->full_name,
                'path' => $folder->path,
                'type' => $folder->type,
                'children' => $this->buildTree($folder->id, $projectId),
            ];
        }

        return $tree;
    }

    protected function buildProjectList($projectId, $parentId = null, $q = null)
    {
        $query = FileEntry::query();
        $query->where('project_id', $projectId);

        if (!empty($parentId)) {
            $query->where('parent_id', $parentId);
        }

        if (!empty($q)) {
            $query->where('name', 'LIKE', '%' . $q . '%');
        }

        $query->orderBy('name');
        $items = $query->get();

        $files = $items->where('type', 'file')->values();
        $folders = $items->where('type', '!=', 'file')->values();

        return [
            'files' => $files,
            'folders' => $folders,
        ];
    }

    /**
     * Завантажити файл
     * @param Request $request
     * @return mixed
     */
    public function uploadFile(Request $request)
    {
        $data = $request->validate([
            'files' => 'required|array',
            'files.*' => 'file',
            'project_id' => 'required|integer|exists:projects,id',
            'parent_id' => 'nullable|integer|exists:file_entries,id',
        ]);

        $projectId = $data['project_id'];
        $parentId = $data['parent_id'] ?? null;

        if ($parentId) {
            $parentFolder = FileEntry::findOrFail($parentId);
            $path = $parentFolder->path;
        } else {
            $path = "projects/$projectId";
        }

        $uploadedFiles = [];

        foreach ($request->file('files') as $file) {
            $filePath = $file->store($path, 'public');
            $fullName = basename($filePath);

            $fileEntry = FileEntry::create([
                'name' => $file->getClientOriginalName(),
                'full_name' => $fullName,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'type' => 'file',
                'parent_id' => $parentId,
                'project_id' => $projectId,
                'path' => $filePath,
            ]);

            $uploadedFiles[] = $fileEntry;
        }

        return $this->responseSuccess([
            'message' => 'Файлы успешно загружены',
            'files' => $uploadedFiles,
        ]);
    }

    /**
     * Перенести файл
     * @param Request $request
     * @return mixed
     */
    public function moveFile(Request $request)
    {
        $data = $request->validate([
            'file_id' => 'required|integer|exists:file_entries,id',
            'new_parent_id' => 'nullable|integer|exists:file_entries,id',
        ]);

        $file = FileEntry::where('type', 'file')->findOrFail($data['file_id']);
        $newParentId = $data['new_parent_id'] ?? null;

        if ($newParentId) {
            $newParent = FileEntry::where('type', 'folder')->findOrFail($newParentId);
            $newPath = $newParent->path . '/' . $file->full_name;
        } else {
            $newPath = "projects/{$file->project_id}/" . $file->full_name;
        }

        if (Storage::exists($file->path)) {
            Storage::move($file->path, $newPath);
        } else {
            return $this->responseSuccess(['error' => 'Файл не найден в файловой системе'], 404);
        }

        $file->update([
            'path' => $newPath,
            'parent_id' => $newParentId,
        ]);

        return $this->responseSuccess([
            'message' => 'Файл успешно перемещен',
            'file' => $file,
        ]);
    }

    /**
     * Видалити файл
     * @param Request $request
     * @return mixed
     */
    public function deleteFile(Request $request)
    {
        $data = $request->validate([
            'file_id' => 'required|integer|exists:file_entries,id',
        ]);

        $file = FileEntry::where('type', 'file')->findOrFail($data['file_id']);

        if (Storage::exists($file->path)) {
            Storage::delete($file->path);
        }

        $file->delete();

        return response()->json([
            'message' => 'Файл успешно удален',
        ]);
    }

}

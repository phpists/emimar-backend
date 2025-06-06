<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\HasJsonResponses;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

abstract class CoreController extends Controller
{
    use HasJsonResponses;

    /**
     * Залогиненный пользователь
     */
    protected function user(): User
    {
        return auth()->user();
    }

    /**
     * Возвращает пользователя по id
     * @param int $userId
     * @return User|null
     */
    protected function userHasById(int $userId): ?User
    {
        return $this->account()->getUserById($userId);
    }

    /**
     * Возвращает кол-во записей на странице по умолчанию
     * @param int $defaultPerPage
     * @return int
     */
    protected function getPerPage(int $defaultPerPage = 15): int
    {
        return (int) request()->get('perPage', $defaultPerPage);
    }

    /**
     * Set sorting in query builder
     * @param $builder
     * @param array $fieldsMapping
     */
    protected function setSorting($builder, $fieldsMapping = [])
    {
        $sortBy = request()->get('sortBy');

        if ($sortBy) {
            if (array_key_exists($sortBy, $fieldsMapping)) {
                if (is_callable($fieldsMapping[$sortBy])) {
                    $fieldsMapping[$sortBy]($builder);
                } else {
                    $builder->orderBy($fieldsMapping[$sortBy], $this->getSortingDirection());
                }
            } else {
                $builder->orderBy($sortBy, $this->getSortingDirection());
            }
        }
    }

    protected function getSortingDirection()
    {
        $sortDesc = request()->get('sortDesc', false);
        if ($sortDesc === 'false') {
            $sortDesc = false;
        }

        return ($sortDesc ? 'DESC' : 'ASC');
    }
}

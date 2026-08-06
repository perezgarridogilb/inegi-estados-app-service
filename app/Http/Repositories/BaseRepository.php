<?php

namespace App\Repositories;

use App\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BaseRepository implements RepositoryInterface
{
    /**
     * @var Model
     */
    private $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id, array $columns = ['*'], array $relations = []): Model
    {
        return $this->findByCriteria(['id' => $id], $columns, $relations);
    }

    /**
     * @inheritDoc
     */
    public function findByUuid(string $uuid, array $columns = ['*'], array $relations = []): Model
    {
        return $this->findByCriteria(['uuid' => $uuid], $columns, $relations);
    }

    /**
     * @inheritDoc
     */
    public function findByCriteria(array $criteria, array $columns = ['*'], array $relations = []): Model
    {
        return $this->query()->select($columns)->with($relations)->where($criteria)->firstOrFail();
    }

    /**
     * @inheritDoc
     */
    public function findByCriteriaNull(array $criteria, array $columns = ['*'], array $relations = [])
    {
        return $this->query()->select($columns)->with($relations)->where($criteria)->first();
    }

    /**
     * @inheritDoc
     */
    public function getByCriteria(array $criteria, array $columns = ['*'], array $relations = []): Collection
    {
        return $this->query()->select($columns)->with($relations)->where($criteria)->get();
    }

    /**
     * @inheritDoc
     */
    public function create(array $attributes): Model
    {
        return $this->query()->create($attributes);
    }

    /**
     * @inheritDoc
     */
    public function updateOrCreate(array $where = [], array $data = []): Model
    {
        return $this->query()->updateOrCreate($where, $data);
    }

    /**
     * @inheritDoc
     */
    public function update(Model $model, array $attributes): void
    {
        $model->update($attributes);
    }

    /**
     * @inheritDoc
     */
    public function delete(Model $model): void
    {
        $model->delete();
    }

    /**
     * @inheritDoc
     */
    public function query(): Builder
    {
        return $this->model->query();
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $id): void
    {
        $model = $this->findById($id);
        if ($model) {
            $model->delete();
        }
    }
}

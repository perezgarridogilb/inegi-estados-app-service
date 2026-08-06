<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface RepositoryInterface
{
    /**
     * Find Model by id
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findById(int $id, array $columns = ['*'], array $relations = []): Model;

    /**
     * Find Model by uuid
     *
     * @param string $uuid
     * @param array $columns
     * @param array $relations
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findByUuid(string $uuid, array $columns = ['*'], array $relations = []): Model;

    /**
     * Find Model by criteria
     *
     * @param array $criteria
     * @param array $columns
     * @param array $relations
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findByCriteria(array $criteria, array $columns = ['*'], array $relations = []): Model;

    /**
     * Find Model by criteria or return null\empty
     *
     * @param array $criteria
     * @param array|string[] $columns
     * @param array $relations
     * @return mixed
     */
    public function findByCriteriaNull(array $criteria, array $columns = ['*'], array $relations = []);

    /**
     * Get Model by criteria
     *
     * @param array $criteria
     * @param array $columns
     * @param array $relations
     * @return Collection
     */
    public function getByCriteria(array $criteria, array $columns = ['*'], array $relations = []): Collection;

    /**
     * Create model with attributes
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model;

    /**
     * Update model with attributes
     *
     * @param Model $model
     * @param array $attributes
     * @return void
     */
    public function update(Model $model, array $attributes): void;

    /**
     * Delete a Model
     *
     * @param Model $model
     * @return void
     */
    public function delete(Model $model): void;

    /**
     * Return a Model query builder
     *
     * @return Builder
     */
    public function query(): Builder;

    /**
     * Delete a model by id
     *
     * @param int $id
     * @return void
     */
    public function deleteById(int $id): void;

    /**
     * Update or create model
     *
     * @param array $where
     * @param array $data
     *
     * @return void
     */
    public function updateOrCreate(array $where = [], array $data = []): Model;
}

<?php

namespace App\Repositories;

use App\Models\State;

final class StatesRepository extends BaseRepository
{
    /* private $model; */

    public function __construct(State $model)
    {
        // para '$this->model = $model;' usar:
        parent::__construct($model);
    }
}

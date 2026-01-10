<?php

namespace App\Http\Resources\Api\Masterdata\Concerns;

use Illuminate\Database\Eloquent\Model;

trait InstanceByModelResource
{
    /**
     * @param Model $model
     * @return self
     */
    public function instanceByModelResource($model)
    {
        $snakeMap = $this->getSnakeCased();
        $modelCasts = $model->getCasts();
        $data = [];
        foreach ($snakeMap as $key => $sn) {
            $data[$key] = $model->$sn;
        }
        return new self($data);
    }

    abstract public function getSnakeCased();

    /**
     * @param array<Model> $models
     * @return self[]
     */
    public function createFromModels($models)
    {
        $instances = [];
        foreach ($models as $model) {
            $instances[] = $this->instanceByModelResource($model);
        }
        return $instances;
    }
}

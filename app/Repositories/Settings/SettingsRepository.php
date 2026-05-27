<?php

namespace App\Repositories\Settings;

use Illuminate\Database\Eloquent\Model;

class SettingsRepository
{
    public function firstOrCreate(string $modelClass, array $attributes = [], array $values = []): Model
    {
        return $modelClass::query()->firstOrCreate($attributes, $values);
    }

    public function updateFirst(string $modelClass, array $data, array $attributes = []): Model
    {
        $model = $this->firstOrCreate($modelClass, $attributes);
        $model->fill($data);
        $model->save();

        return $model;
    }

    public function upsertBy(string $modelClass, string $column, mixed $value, array $data): Model
    {
        return $modelClass::query()->updateOrCreate([$column => $value], $data);
    }
}

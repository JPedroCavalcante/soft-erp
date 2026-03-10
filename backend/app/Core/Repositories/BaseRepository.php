<?php

namespace App\Core\Repositories;

use App\Core\Traits\LogsRepositoryActions;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements RepositoryInterface
{
    use LogsRepositoryActions;

    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(int $perPage = 10)
    {
        $this->logAction('index', null, ['per_page' => $perPage]);
        return $this->model->latest()->paginate($perPage);
    }

    public function allWithoutPagination()
    {
        $this->logAction('index_all');
        return $this->model->latest()->get();
    }

    public function find(int $id)
    {
        $record = $this->model->findOrFail($id);
        $this->logAction('show', $record);
        return $record;
    }

    public function create(array $data)
    {
        try {
            $record = $this->model->create($data);
            $this->logAction('create', $record, $data);
            return $record;
        } catch (\Throwable $e) {
            $this->logError('create', $e, ['data' => $data]);
            throw $e;
        }
    }

    public function update(int $id, array $data)
    {
        try {
            $record = $this->find($id);
            $record->update($data);
            $this->logAction('update', $record, $data);
            return $record;
        } catch (\Throwable $e) {
            $this->logError('update', $e, ['id' => $id, 'data' => $data]);
            throw $e;
        }
    }

    public function delete(int $id)
    {
        try {
            $record = $this->find($id);
            $result = $record->delete();
            $this->logAction('delete', $id);
            return $result;
        } catch (\Throwable $e) {
            $this->logError('delete', $e, ['id' => $id]);
            throw $e;
        }
    }
}

<?php

namespace App\Core\Traits;

use Illuminate\Support\Facades\Log;

trait LogsRepositoryActions
{
    protected function logAction(
        string $action,
        mixed $model = null,
        array $data = [],
        string $level = 'info'
    ): void {
        $modelName = class_basename($this->model);
        $repositoryName = class_basename($this);

        $logData = [
            'action' => $action,
            'model' => $modelName,
            'repository' => $repositoryName,
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ];

        if ($model) {
            $logData['model_id'] = is_object($model) ? $model->id : $model;
        }

        if (!empty($data)) {
            $logData['data'] = $this->sanitizeLogData($data);
        }

        $message = sprintf(
            '%s: %s #%s by user #%s',
            $action,
            $modelName,
            $logData['model_id'] ?? 'N/A',
            $logData['user_id'] ?? 'guest'
        );

        Log::channel('repository')->$level($message, $logData);
    }

    protected function sanitizeLogData(array $data): array
    {
        $sensitiveKeys = ['password', 'token', 'secret', 'api_key', 'authorization'];

        foreach ($sensitiveKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = '***REDACTED***';
            }
        }

        return $data;
    }

    protected function logError(string $action, \Throwable $exception, array $context = []): void
    {
        $modelName = class_basename($this->model);

        Log::channel('repository')->error(
            sprintf('%s failed for %s: %s', $action, $modelName, $exception->getMessage()),
            [
                'action' => $action,
                'model' => $modelName,
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'timestamp' => now()->toISOString(),
                'context' => $context,
            ]
        );
    }
}

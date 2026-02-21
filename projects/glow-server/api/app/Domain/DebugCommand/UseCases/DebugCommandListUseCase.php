<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases;

use App\Domain\DebugCommand\UseCases\Commands\BaseParameterizedCommands;
use Illuminate\Support\Str;

class DebugCommandListUseCase
{
    /**
     * デバッグコマンドのリストを作成する
     * @return array<mixed>
     */
    public function exec(): array
    {
        $results = [];
        $path = app_path('Domain/DebugCommand/UseCases/Commands');
        $files = scandir($path);
        foreach ($files as $file) {
            $filePath = $path . DIRECTORY_SEPARATOR . $file;
            if (is_file($filePath) && Str::endsWith($file, 'UseCase.php')) {
                $className = 'App\\Domain\\DebugCommand\\UseCases\\Commands\\' . pathinfo($file, PATHINFO_FILENAME);
                if (class_exists($className)) {
                    $commandUseCase = app()->make($className);
                    $result = [
                        'command' => Str::before($file, 'UseCase.php'),
                        'name' => $commandUseCase->getName(),
                        'description' => $commandUseCase->getDescription(),
                    ];

                    // BaseParameterizedCommandsを継承している場合のみパラメータ情報を追加
                    if ($commandUseCase instanceof BaseParameterizedCommands) {
                        $result['requiredParameters'] = $commandUseCase->getRequiredParameters();
                    }

                    $results['commands'][] = $result;
                }
            }
        }
        return $results;
    }
}

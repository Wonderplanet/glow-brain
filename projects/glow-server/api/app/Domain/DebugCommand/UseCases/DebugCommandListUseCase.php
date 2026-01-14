<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\UseCases;

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
                    $results['commands'][] = [
                        'command' => Str::before($file, 'UseCase.php'),
                        'name' => $commandUseCase->getName(),
                        'description' => $commandUseCase->getDescription(),
                    ];
                }
            }
        }
        return $results;
    }
}

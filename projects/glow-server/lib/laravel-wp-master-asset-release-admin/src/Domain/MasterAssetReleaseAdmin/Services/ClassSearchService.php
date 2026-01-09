<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Services;

use Composer\Autoload\ClassLoader;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// git pullなどで動的に配置されたクラスはdump-autoloadしないとopcache等でクラスマップがキャッシュされて読めないことがあるため、
// Composerのクラスロードに失敗した際はMasterとStructクラスのファイルを直接読めるようクラスローダを登録する
// 影響範囲はこのクラス内のみ
spl_autoload_register(function ($fullClassName) {
    $baseDirs = [
        base_path() . '/../share/app/Models/',
        base_path() . '/../share/app/Http/Structs/',
    ];

    // \App\Models\XXX や \App\Http\Structs\XXX でクラス指定されるので、XXXの部分を取得
    $classNames = explode('\\', $fullClassName);
    $className = end($classNames);
    foreach ($baseDirs as $baseDir) {
        $file = $baseDir . $className . '.php';
        if (file_exists($file)) {
            // ファイルが存在すればそれを読み込む
            require $file;
            return true;
        }
    }
    return false;
}, true, false);

/***
 * マスターデータ管理ツールv1、v2共通で使用するクラス
 */
class ClassSearchService
{
    /**
     * クラス名がAppに定義されているモデルか判定
     * 定義されていればtrue
     *
     * @param string $className
     * @return bool
     */
    public function verifyMasterModelClassName(string $className): bool
    {
        try {
            if (
                class_exists(config('wp_master_asset_release_admin.masterResourceModelsPath.mst') . $className) || class_exists(config('wp_master_asset_release_admin.masterResourceModelsPath.mst') . Str::singular($className)) ||
                class_exists(config('wp_master_asset_release_admin.masterResourceModelsPath.opr') . $className) || class_exists(config('wp_master_asset_release_admin.masterResourceModelsPath.opr') . Str::singular($className)) ||
                class_exists("\App\Models\\" . $className) || class_exists("\App\Models\\" . Str::plural($className))
            ) {
                return true;
            }
        } catch(\Throwable $e) {
            // クラスが存在しない場合はオートローダがクラスのphpファイルをロードできないためErrorExceptionになる
            Log::debug($e->getMessage());
            return false;
        }
        return false;
    }

    /**
     * ModelsのMasterクラスを作って返す
     * クラスがなければnull
     *
     * @param string $title
     * @return mixed
     */
    public function createMasterModelClass(string $title): mixed
    {
        if ($this->verifyMasterModelClassName($title)) {
            if (class_exists(config('wp_master_asset_release_admin.masterResourceModelsPath.mst') . $title)) {
                return new (config('wp_master_asset_release_admin.masterResourceModelsPath.mst') . $title);
            } else if (class_exists(config('wp_master_asset_release_admin.masterResourceModelsPath.mst') . Str::singular($title))){
                return new (config('wp_master_asset_release_admin.masterResourceModelsPath.mst') . Str::singular($title));
            } else if (class_exists(config('wp_master_asset_release_admin.masterResourceModelsPath.opr') . $title)) {
                return new (config('wp_master_asset_release_admin.masterResourceModelsPath.opr') . $title);
            } else if (class_exists(config('wp_master_asset_release_admin.masterResourceModelsPath.opr') . Str::singular($title))) {
                return new (config('wp_master_asset_release_admin.masterResourceModelsPath.opr') . Str::singular($title));
            } else if (class_exists("\App\Models\\" . $title)) {
                return new ("\App\Models\\" . $title);
            } else if (class_exists("\App\Models\\" . Str::plural($title))) {
                return new ("\App\Models\\" . Str::plural($title));
            }
        }
        return null;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getStructModelClassName(string $name): string
    {
        $camel = str_replace("master", "", Str::camel(Str::singular($name))); // Pascalケースに変換
        try {
            if (class_exists("\App\Http\Structs\\" . $camel . "Struct")) {
                return "\App\Http\Structs\\" . $camel . "Struct";
            }
        } catch(\Throwable $e) {
            // クラスが存在しない場合はオートローダがクラスのphpファイルをロードできないためErrorExceptionになる
            Log::debug($e->getMessage());
        }
        return "";
    }

    /**
     * ネームスペース直下のクラス名一覧を返す
     * @param string $namespace
     * @return string[]
     */
    public function getClassNamesInNameSpace(string $namespace): array
    {
        $autoLoaders = ClassLoader::getRegisteredLoaders();

        $classes = [];
        $namespaceLength = strlen($namespace);
        foreach ($autoLoaders as $autoloader) {
            foreach ($autoloader->getClassMap() as $class => $path) {
                $subNamespace = substr($class, $namespaceLength + 1);
                if (str_starts_with($class, $namespace) && !str_contains($subNamespace, '\\')) {
                    $classes[] = $class;
                }
            }
        }

        return array_unique($classes);
    }
}

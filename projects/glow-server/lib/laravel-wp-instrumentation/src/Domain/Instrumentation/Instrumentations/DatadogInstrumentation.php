<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Instrumentation\Instrumentations;

use Composer\Autoload\ClassLoader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use WonderPlanet\Domain\Instrumentation\Services\DatadogTraceService;

/**
 * DataDogの計装設定
 *
 * 軽装の設定を細かく変更する場合は、
 * DatadogInstrumentationクラスを継承しくクラスをwp_dateadog.instrumentationに設定する
 *
 * 各constの定義を継承して書き換えた場合を考慮してstaticで参照すること
 */
readonly class DatadogInstrumentation
{
    /**
     * コンストラクタ
     *
     * @param DatadogTraceService $datadogTraceService
     */
    public function __construct(
        private readonly DatadogTraceService $datadogTraceService
    ) {
    }

    // 各フィルタの取得メソッド
    /**
     * 計測対象の名前空間を取得
     *
     * @return array<string>
     */
    public function getEnableNamespaces(): array
    {
        return config('wp_instrumentation.filter.enable_namespaces', []);
    }

    /**
     * 計測から除外する名前空間を取得
     *
     * @return array<string>
     */
    public function getExclusionNamespaces(): array
    {
        return config('wp_instrumentation.filter.exclusion_namespaces', []);
    }

    /**
     * 前方一致で除外する名前空間を取得
     *
     * @return array<string>
     */
    public function getExclusionStartWithNamespaces(): array
    {
        return config('wp_instrumentation.filter.exclusion_start_with_namespaces', []);
    }

    /**
     * 除外クラスを取得
     *
     * @return array<string>
     */
    public function exclusionClasses(): array
    {
        return config('wp_instrumentation.filter.exclusion_classes', []);
    }

    /**
     * 前方一致で除外するメソッドの設定を取得
     *
     * @return array<string>
     */
    public function exclusionMethodsStartWith(): array
    {
        return config('wp_instrumentation.filter.exclusion_methods.start_with', []);
    }

    /**
     * 正規表現で除外するメソッドの設定を取得
     *
     * @return array<string>
     */
    public function exclusionMethodsRegex(): array
    {
        return config('wp_instrumentation.filter.exclusion_methods.regex', []);
    }

    /**
     * Datadog向けの計装を行う
     *
     * @return void
     */
    public function registInstrumentation(): void
    {
        // DataDogの計装が無効なら何もしない
        if (!$this->datadogTraceService->enable()) {
            return;
        }

        $this->datadogTraceService->traceScope(
            self::class . '::registInstrumentation',
            function () {
                // トレースデータ登録
                $traceTargets = null;
                if (config('wp_instrumentation.cache.enable')) {
                    $request = app(Request::class);
                    $cacheKey = $request->path() . ':' . $this->makeConditionsHash();
                    $traceTargets = Cache::store('apc')->get($cacheKey);
                }
                $autoLoaders = null;
                if (is_null($traceTargets)) {
                    $autoLoaders = ClassLoader::getRegisteredLoaders();
                    $traceTargets = $this->targetTraceList($autoLoaders);
                }
                $this->datadogTraceService->setTraceMethod($traceTargets);

                if (config('wp_instrumentation.cache.enable')) {
                    Cache::store('apc')->put($cacheKey, $traceTargets);
                }
            }
        );
    }


    /**
     * 定義された除外条件のハッシュを生成する
     *
     * @return string
     */
    private function makeConditionsHash(): string
    {
        return md5(serialize([
            // 対象条件をハッシュ化
            $this->getEnableNamespaces(),
            $this->getExclusionNamespaces(),
            $this->getExclusionStartWithNamespaces(),
            $this->exclusionClasses(),
            $this->exclusionMethodsStartWith(),
            $this->exclusionMethodsRegex(),
        ]));
    }

    /**
     * 対象のトレースメソッドリストを返す
     *
     * @param array<string, ClassLoader> $autoLoaders
     * @return array<string, array<string>> {クラス名: [メソッド名]}
     */
    protected function targetTraceList(array $autoLoaders): array
    {
        // クラス一覧を取得
        $classes = $this->targetTraceClassList($autoLoaders);

        // メソッド一覧を取得
        $methods = $this->targetTraceMethodList($classes);

        return $methods;
    }


    /**
     * トレース対象のクラスリストを取得
     *
     * @param array<string, ClassLoader> $autoLoaders
     * @return array<string>
     */
    protected function targetTraceClassList(array $autoLoaders): array
    {
        $classes = $this->datadogTraceService->traceScope(
            self::class . '::targetTraceClassList',
            function () use ($autoLoaders) {
                $classes = [];
                $enableNamespaces = $this->getEnableNamespaces();
                $exclusionNamespaces = $this->getExclusionNamespaces();
                $exclusionStartWithNamespaces = $this->getExclusionStartWithNamespaces();
                $exclusionClasses = $this->exclusionClasses();

                foreach ($autoLoaders as $autoloader) {
                    foreach ($autoloader->getClassMap() as $class => $path) {
                        // 対象クラスかを判定
                        if (
                            !$this->isTargetClass(
                                (string)$class,
                                $enableNamespaces,
                                $exclusionNamespaces,
                                $exclusionStartWithNamespaces,
                                $exclusionClasses
                            )
                        ) {
                            continue;
                        }

                        // クラスを登録
                        $classes[] = $class;
                    }
                }

                return $classes;
            }
        );

        return $classes ?? [];
    }

    /**
     * クラス名より計測対象かどうかを判定する
     *
     * @param string $class
     * @param array<string> $enableNamespaces
     * @param array<string> $exclusionNamespaces
     * @param array<string> $exclusionStartWithNamespaces
     * @param array<string> $exclusionClasses
     *
     * @return boolean
     */
    private function isTargetClass(
        string $class,
        array $enableNamespaces,
        array $exclusionNamespaces,
        array $exclusionStartWithNamespaces,
        array $exclusionClasses,
    ): bool {
        $flg = false;

        if (count($enableNamespaces) !== 0) {
            foreach ($enableNamespaces as $namespace) {
                if (str_starts_with($class, $namespace)) {
                    $flg = true;
                    break;
                }
            }
            if ($flg === false) {
                return false;
            }
        }
        $exclusion = false;
        foreach ($exclusionNamespaces as $namespace) {
            if (preg_match("/$namespace/", $class)) {
                $exclusion = true;
                break;
            }
        }
        if ($exclusion === true) {
            return false;
        }
        $exclusion = false;
        foreach ($exclusionStartWithNamespaces as $namespace) {
            if (str_starts_with($class, $namespace)) {
                $exclusion = true;
                break;
            }
        }
        if ($exclusion === true) {
            return false;
        }
        foreach ($exclusionClasses as $excludeClass) {
            // クラスが一致していてもスキップ
            if ($class === $excludeClass) {
                $exclusion = true;
                break;
            }
            // 除外クラスが親クラスにあればスキップ
            // サブクラス検索時にエラーになる場合はスキップする
            try {
                if (is_subclass_of($class, $excludeClass)) {
                    $exclusion = true;
                    break;
                }
            } catch (\Throwable $e) {
                \Log::debug("is_subclass_of error: $class, $excludeClass, " . $e->getMessage());
                $exclusion = true;
                break;
            }
        }
        if ($exclusion === true) {
            return false;
        }

        // クラスが存在しなければスキップ
        // クラスが存在しない場合はエラーになるため、try-catchで囲む
        try {
            if (!class_exists($class)) {
                return false;
            }
        } catch (\Throwable $e) {
            \Log::debug("class_exists error: $class, " . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * トレース対象のメソッドリストを取得
     *
     * @param array<string> $classes
     * @return array<string, array<string>> {クラス名: [メソッド名]}
     */
    public function targetTraceMethodList(array $classes): array
    {
        $result = $this->datadogTraceService->traceScope(
            self::class . '::targetTraceMethodList',
            function () use ($classes) {
                $result = [];
                $exclusionMethodsStartWith = $this->exclusionMethodsStartWith();
                $exclusionMethodsRegex = $this->exclusionMethodsRegex();

                foreach ($classes as $className) {
                    $result[$className] = [];

                    $methods = get_class_methods($className);
                    foreach ($methods as $methodName) {
                        // メソッドの除外判定
                        $exclusion = false;
                        foreach ($exclusionMethodsStartWith as $excludeMethod) {
                            if (str_starts_with($className . '::' . $methodName, $excludeMethod)) {
                                $exclusion = true;
                                break;
                            }
                        }
                        if ($exclusion === true) {
                            continue;
                        }
                        foreach ($exclusionMethodsRegex as $excludeMethod) {
                            // メソッド名が正規表現に一致していたら除外
                            if (preg_match("/$excludeMethod/", $className . '::' . $methodName)) {
                                $exclusion = true;
                                break;
                            }
                        }
                        if ($exclusion === true) {
                            continue;
                        }

                        $result[$className][] = $methodName;
                    }
                }

                return $result;
            }
        );

        return $result ?? [];
    }
}

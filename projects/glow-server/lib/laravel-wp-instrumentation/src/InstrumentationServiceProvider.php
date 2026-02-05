<?php

declare(strict_types=1);

namespace WonderPlanet;

use WonderPlanet\Domain\Common\Providers\BaseServiceProvider;

class InstrumentationServiceProvider extends BaseServiceProvider
{
    public const NAME = 'wp_instrumentation';

    /**
     * 処理済みフラグ
     *
     * なぜかProviderが複数回呼ばれているため、一度処理したらそれ以上処理しないようにする
     *
     * @var boolean
     */
    private bool $processed = false;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        // 設定ファイルのマージ
        $this->mergeConfigFrom(
            __DIR__ . '/../config/wp_instrumentation.php',
            'wp_instrumentation'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // 一度処理したらそれ以上処理しない
        if ($this->processed) {
            return;
        }

        // 設定ファイルの登録
        $publishes[__DIR__ . '/../config/wp_instrumentation.php'] = config_path('wp_instrumentation.php');

        $this->publishes($publishes, 'wp');

        // Datadogの計装
        // カスタム軽装のクラスを設定から取得
        $instrumentationClassName = config('wp_instrumentation.datadog.instrumentation');
        if (
            !is_null($instrumentationClassName)
            && $instrumentationClassName !== ''
            && class_exists($instrumentationClassName) === true
        ) {
            /** @var \WonderPlanet\Domain\Instrumentation\Instrumentations\DatadogInstrumentation $instrumentation */
            $instrumentation = app()->make($instrumentationClassName);
            $instrumentation->registInstrumentation();
        }

        $this->processed = true;
    }
}

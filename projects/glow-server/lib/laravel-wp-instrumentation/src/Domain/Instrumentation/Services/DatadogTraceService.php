<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Instrumentation\Services;

use DDTrace\GlobalTracer;
use DDTrace\SpanData;

use function DDTrace\trace_method;

/**
 * DataDogのトレース計装サービス
 *
 * DataDog APMのSDKを呼び出す部分はこのクラスにまとめる
 *
 * ※メソッドおよび取得されるクラスの型が不明な部分はひとまずmixedで対応している
 */
readonly class DatadogTraceService
{
    /**
     * DataDogの計装を有効にするか
     *
     * @return boolean
     */
    public function enable(): bool
    {
        // configファイルで無効になっている場合はfalse
        if (!config('wp_instrumentation.datadog.enable')) {
            return false;
        }

        // クラスが存在していなければfalse
        if (!class_exists('DDTrace\SpanData') || !class_exists('DDTrace\GlobalTracer')) {
            return false;
        }

        // ユニットテストからの実行であればfalse
        if (env('APP_ENV') === 'testing') {
            return false;
        }

        // artisanコマンドの場合は除く
        // octaneでは$_SERVERの値が起動時から変更されなくなるので、requestから取得する
        $request = request();
        if ($this->isExcludeArtisanCommand($request->server->get('argv', []))) {
            return false;
        }

        return true;
    }

    /**
     * 除外対象のartisanコマンドか
     *   $_SERVER['argv']の内容からartisanコマンドかどうかを判定する
     *  除外対象のartisanコマンドの場合はtrueを返す
     *
     * app:で始まるartisanコマンドは、プロダクトで作成した処理が含まれている可能性があるため除外しない
     *
     * @param array<string> $argv
     * @return boolean
     */
    private function isExcludeArtisanCommand(array $argv): bool
    {
        // artisanコマンドであること
        if (($argv[0] ?? '') !== 'artisan') {
            return false;
        }

        // $argv[1]の先頭がapp:で始まっていないこと
        if (strpos(($argv[1] ?? ''), 'app:') === 0) {
            return false;
        }

        return true;
    }

    /**
     * scopeを作成してトレースを行う
     *
     * tracerの取得からcloseまでを一連の処理としてまとめる
     *
     * @param callable $callable
     * @param string $spanName
     * @return mixed $callableの処理結果を返す
     */
    public function traceScope(string $spanName, callable $callable): mixed
    {
        // Datadog計装が無効の場合はコールバックだけ実行する
        if (!$this->enable()) {
            try {
                return $callable();
            } catch (\Throwable $e) {
                \Log::error($e->getMessage(), ['exception' => $e]);
            }
            return null;
        }

        $scope = null;
        try {
            $tracer = GlobalTracer::get();
            $scope = $tracer->startActiveSpan($spanName);
            return $callable();
        } catch (\Throwable $e) {
            \Log::error($e->getMessage(), ['exception' => $e]);
        } finally {
            // scopeをcloseする時、取得したtry-cache内でcloseを呼ばないと
            // 「[ddtrace] [error] There is no user-span on the top of the stack. Cannot close.」
            // というエラーが発生する
            if (!is_null($scope)) {
                $scope->close();
            }
        }
        return null;
    }

    /**
     * クラスとメソッドをトレースする
     *
     * @param array<string, array<string, string>> $traces {クラス名: [メソッド名]}
     * @return void
     */
    public function setTraceMethod(array $traces): void
    {
        $this->traceScope(
            self::class . '::setTraceMethod',
            function () use ($traces) {
                foreach ($traces as $className => $methods) {
                    foreach ($methods as $methodName) {
                        trace_method(
                            $className,
                            $methodName,
                            function (SpanData $span, array $args, $retval, $exception) use ($className, $methodName) {
                                $operationName = $className . '::' . $methodName;
                                $span->name = $operationName;
                                $span->resource = $operationName;
                                $span->service = env('DD_SERVICE');
                            }
                        );
                    }
                }
            }
        );
    }
}

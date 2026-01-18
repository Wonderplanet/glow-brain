<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Common\Providers;

use Illuminate\Support\ServiceProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * WPのサービスプロバイダを管理的に登録するための基底クラス
 *
 * 指定されたクラスをscopedと遅延プロバイダで登録する
 * Domainで登録されるクラスは原則としてscopedで登録する想定のため共通化する
 * 処理から外れる場合は、個別にregisterメソッドを実装すること
 *
 * ※ライブラリなどでboot/registerを遅延プロバイダに関係なく動作させる場合、こちらを規定クラスにする
 *  遅延プロバイダだとregisterが読みこまるタイミングが遅くなるので、
 *  mergeConfigFromなどの処理をregisterで使用していると、適用タイミングが変わってしまう。
 *  クラスの読み込みに関わらず必ず動作させたい場合は、こちらを継承する
 */
abstract class BaseServiceProvider extends ServiceProvider
{
    /**
     * scoped/遅延プロバイダとして登録するクラスのリスト
     *
     * クラス名を指定する
     * Facadeの登録は$facadesに指定する
     *
     * @var array<string>
     */
    protected array $classes = [];

    /**
     * Facadeとして登録するクラスのリスト
     *
     * Facade::getFacadeAccessor()で返す文字列をキー、クラス名を値として指定する
     *
     * @var array<string, string>
     */
    protected array $facades = [];

    /**
     * サービスプロバイダをscopedで登録する
     *
     * @return void
     */
    public function register()
    {
        // クラスの登録
        foreach ($this->classes as $class) {
            $this->app->scoped($class);
        }

        // Facadeの登録
        foreach ($this->facades as $key => $class) {
            $this->app->scoped($key, $class);
        }
    }

    /**
     * ライブラリにあるコードコピー用リソース公開メソッド
     * composer install 実行時に、管理ツールのコードがプロダクト側に存在しない場合、ライブラリ側で用意したコードを再起的にコピーします
     * ライブラリ側にコピー元ディレクトリが存在しない、公開リソースがなかった場合はスキップします
     * コピー先はプロダクト側のapp/以下のみ対象としています
     *
     * @param string $libDirPath コピー元のクラスパス(ライブラリ側のProviderで指定)
     * @return void
     * @throws \Exception
     */
    protected function publishClassFiles(string $libDirPath): void
    {
        $publishes = [];
        if (!is_dir($libDirPath)) {
            // コピー元ディレクトリが存在しない場合はエラー
            throw new \Exception("publish:wp-admin-files No Directory libDirPath: {$libDirPath}");
        }

        // 指定されたディレクトリ内のすべてのファイルを再帰的に取得する
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($libDirPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                // コピー元のパスを基準に、コピー先のパスを作成
                $relativePath = app_path('/') . str_replace($libDirPath, '', $file->getPathname());

                // publishの配列に追加(公開したいクラスのパス(パッケージ側) => 公開先のクラスのパス(プロダクト側))
                $publishes[$file->getPathname()] = $relativePath;
            }
        }

        if ($publishes === []) {
            // 公開対象が空の場合はエラー
            throw new \Exception('publish:wp-admin-files Empty publishes');
        }

        $this->publishes($publishes, 'wp-admin-files');
    }
}

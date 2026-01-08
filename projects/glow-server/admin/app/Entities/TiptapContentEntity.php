<?php

declare(strict_types=1);

namespace App\Entities;

/**
 * TiptapEditor上で入力されたデータの管理と変換処理を行うクラス
 */
class TiptapContentEntity
{
    /**
     * TiptapEditorフォームにアップロードした画像ファイルが保存されるローカルのディレクトリ
     * 新規アップロードされた画像かどうかを判定するために使用している
     *
     * @var string
     * */
    private string $localImageUploadDir = '/storage/images';

    /**
     * TipTapEditorに入力したマークアップデータをtiptap_converterで変換して得られたJSON
     * @var array<mixed>
     */
    private array $elements = [];

    /**
     *　画像ファイル名リスト
     * @var array
     */
    private array $imageFileNames = [];

    /**
     * リモートへアップロードが必要な画像ファイル名リスト
     * @var array
     */
    private array $needRemoteUploadImageFileNames = [];

    public function __construct(
        array | string $elements,
    ) {
        $this->setElements($elements);
        $this->setImageFileNames();
    }

    private function setElements(array | string $elements): void
    {
        if (is_array($elements)) {
            $elements = tiptap_converter()->asHTML($elements);
        }
        // マークアップエディターに、直接htmlを入力した場合、エスケープが入るので、それを取り除く
        $elements = html_entity_decode($elements, ENT_QUOTES, 'UTF-8');
        $this->elements = tiptap_converter()->asJSON($elements, true);
    }

    /**
     * リモート(s3など)へアップロードが必要な画像ファイル名を取得してセットする
     */
    private function setImageFileNames(): void
    {
        $imageSrcList = $this->getAllImageSrcList();

        $imageFileNames = [];
        $needRemoteUploadImageFileNames = [];

        foreach ($imageSrcList as $imageSrc) {
            $fileName = basename($imageSrc);

            $imageFileNames[] = $fileName;

            if (str_starts_with($imageSrc, $this->localImageUploadDir) === false) {
                continue;
            }
            $needRemoteUploadImageFileNames[] = $fileName;
        }

        $this->needRemoteUploadImageFileNames = $needRemoteUploadImageFileNames;
        $this->imageFileNames = $imageFileNames;
    }

    public function getImageFileNames(): array
    {
        return $this->imageFileNames;
    }

    public function getNeedRemoteUploadImageFileNames(): array
    {
        return $this->needRemoteUploadImageFileNames;
    }

    public function setLocalImageUploadDir(string $localImageUploadDir): void
    {
        $this->localImageUploadDir = $localImageUploadDir;
    }

    /**
     * 本文のhtml内の画像パスを取得するの、再帰処理メソッド
     *
     * @param array<mixed> $content TipTapEditorに入力したマークアップデータをtiptap_converterで変換して得られたJSON
     * @return array
     */
    private function getImageSrcList(array $elements): array
    {
        $images = [];
        foreach ($elements['content'] as $element) {
            if ($element['type'] === 'image') {
                $images[] = $element['attrs']['src'] ?? '';
            }

            if (isset($element['content']) && is_array($element['content'])) {
                $images = array_merge($images, $this->getImageSrcList($element));
            }
        }
        return $images;
    }

    public function getAllImageSrcList(): array
    {
        return $this->getImageSrcList($this->elements);
    }

    /**
     * 画像パスを置換する
     * @param array<mixed> $elements TipTapEditorに入力したマークアップデータをtiptap_converterで変換して得られたJSON
     * @param callable $convertSrcCallback 画像パスを変換するコールバック関数。引数には変換前のパス、戻り値には変換後のパスを返す
     * @return void
     */
    private function replaceImageSrc(array &$elements, callable $convertSrcCallback) {
        foreach ($elements['content'] as &$content) {
            if ($content['type'] === 'image') {
                if (isset($content['attrs']['src'])) {
                    $src = $content['attrs']['src'];

                    $convertedSrc = $convertSrcCallback($src);

                    $content['attrs']['src'] = $convertedSrc;
                }
            }

            if (isset($content['content']) && is_array($content['content'])) {
                $this->replaceImageSrc($content, $convertSrcCallback);
            }
        }
    }

    /**
     * 指定した方法で画像パスを置換した要素を取得する
     * @param callable $convertSrcCallback
     * @return array
     */
    public function getImageSrcReplacedElements(callable $convertSrcCallback): array
    {
        $elements = $this->elements;
        $this->replaceImageSrc($elements, $convertSrcCallback);
        return $elements;
    }
}

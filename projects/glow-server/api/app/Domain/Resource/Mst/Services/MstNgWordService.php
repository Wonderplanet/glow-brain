<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstNgWordEntity;
use App\Domain\Resource\Mst\Repositories\MstNgWordRepository;
use App\Domain\Resource\Mst\Repositories\MstWhiteWordRepository;
use Illuminate\Support\Collection;

class MstNgWordService
{
    public function __construct(
        private readonly MstNgWordRepository $mstNgWordRepository,
        private readonly MstWhiteWordRepository $mstWhiteWordRepository,
    ) {
    }

    /**
     * NGワードが対象文字列に含まれていないか検証する
     * @param string $text
     * @param int    $errorCode
     * @return void
     * @throws GameException
     */
    public function validateNGWord(string $text, int $errorCode): void
    {
        // 絵文字判定
        if (preg_match('/[\xF0-\xF7][\x80-\xBF][\x80-\xBF][\x80-\xBF]/', $text)) {
            throw new GameException($errorCode, 'name contains emojis.');
        }

        // 常用ではない４byte漢字判定
        // patternを1行で定義すると120文字を超え静的解析エラーになるので分割して結合する
        $pattern = '/[';
        $pattern .= '\x{20000}-\x{2A6DF}\x{2A700}-\x{2B73F}\x{2B740}-\x{2B81F}';
        $pattern .= '\x{2B820}-\x{2CEAF}\x{2CEB0}-\x{2EBEF}\x{2F800}-\x{2FA1F}';
        $pattern .= ']/u';
        if (preg_match($pattern, $text)) {
            throw new GameException(
                $errorCode,
                'name contains uncommon kanji characters.'
            );
        }

        // 機種依存文字判定
        // @see https://mgng.mugbum.info/60%EF%BC%89
        $convertedLength = strlen(
            mb_convert_encoding(mb_convert_encoding($text, 'SJIS', 'UTF-8'), 'UTF-8', 'SJIS')
        );
        if (strlen($text) !== $convertedLength) {
            throw new GameException(
                $errorCode,
                'name contains environment-department characters.'
            );
        }

        // 2byte罫線文字判定
        if (preg_match('/[\x{2500}-\x{257F}]/u', $text)) {
            throw new GameException(
                $errorCode,
                'name contains box drawing characters.'
            );
        }

        // ギリシャ文字判定
        if (preg_match('/[\x{0370}-\x{03FF}\x{1F00}-\x{1FFF}]/u', $text)) {
            throw new GameException(
                $errorCode,
                'name contains greek characters.'
            );
        }

        // ロシア文字判定
        if (preg_match('/[\x{0400}-\x{04FF}]/u', $text)) {
            throw new GameException(
                $errorCode,
                'name contains russian characters.'
            );
        }

        // 空白文字
        if (preg_match('/( |　)/', $text)) {
            throw new GameException(
                $errorCode,
                'name contains space characters.'
            );
        }

        // 全NGワードをチェック
        $mstNgWords = $this->mstNgWordRepository->getNgWordAll();
        if ($mstNgWords->isEmpty()) {
            return;
        }
        // ホワイトワードを取得
        $mstWhiteWords = $this->mstWhiteWordRepository->getWhiteWordAll();

        // 入力文字列の寄せを行う
        $text = $this->convertKana($text);
        // ホワイトワードを除外
        $text = $this->exclusionWhiteWords($text, $mstWhiteWords);

        $ngWords = $mstNgWords->mapWithKeys(function (MstNgWordEntity $ngWord) {
            // NGワード文字列の寄せを行う
            $from = $this->convertKana($ngWord->getWord());
            $to = str_repeat('*', mb_strlen($from));
            return [$from => $to];
        })->toArray();

        $strtrBeforeName = strtr($text, $ngWords);

        // NGワードが含まれており、置換されている場合はエラー
        if ($strtrBeforeName !== $text) {
            throw new GameException(ErrorCode::PLAYER_NAME_USED_NG_WORD);
        }
    }

    /**
     * ホワイトワードをNGワードチェックの対象文字列から除外する
     * @param string $text
     * @param Collection $mstWhiteWords
     * @return string
     */
    private function exclusionWhiteWords(string $text, Collection $mstWhiteWords): string
    {
        // ホワイトワードが存在しない場合はそのまま返す
        if ($mstWhiteWords->isEmpty()) {
            return $text;
        }
        // ホワイトワードを除外
        foreach ($mstWhiteWords as $whiteWord) {
            // ホワイトワード文字列の寄せを行う
            $whiteWord = $this->convertKana($whiteWord->getWord());
            // ホワイトワードにマッチしたら対象文字列から除外
            $text = str_replace($whiteWord, '', $text);
        }
        return $text;
    }

    /**
     * 対象文字列を半角英数小文字、全角かなに寄せる
     * @param string $text
     * @return string
     */
    private function convertKana(string $text): string
    {
        // 濁点統合のVを使うために一旦半角カナに変換
        $text = mb_convert_kana($text, 'hk', 'UTF-8');
        // 全角英数を半角英数に変換 a
        // 半角カナを全角かなに変換　濁点付きの文字を一文字に変換 HV
        $text = mb_convert_kana($text, 'aHV', 'UTF-8');
        // 大文字を小文字に変換
        $text = mb_strtolower($text, 'UTF-8');
        return $text;
    }
}

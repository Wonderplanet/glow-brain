<?php

namespace Tests\Feature\Domain\Common\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Models\MstNgWord;
use App\Domain\Resource\Mst\Models\MstWhiteWord;
use App\Domain\Resource\Mst\Services\MstNgWordService;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class MstNgWordServiceTest extends TestCase
{
    private MstNgWordService $mstNgWordService;

    public function setUp(): void
    {
        parent::setUp();
        $this->mstNgWordService = $this->app->make(MstNgWordService::class);
    }

    public function testValidateNGWord_NGワードを含まない()
    {
        MstNgWord::factory()->create(['word' => 'test']);

        $this->mstNgWordService->validateNGWord('aaa', ErrorCode::PLAYER_NAME_USED_NG_WORD);
        $this->assertTrue(true);
    }

    public static function params_NGワードを含む()
    {
        return [
            '大文字変換' => [
                'input' => 'ABC',
                'ngWord' => 'abc',
                'whiteWord' => 'aaa',
            ],
            'カナ変換' => [
                'input' => 'あいう',
                'ngWord' => 'アイウ',
                'whiteWord' => 'aaa',
            ],
             '半角カナ変換' => [
                'input' => 'ｱｲｳ',
                'ngWord' => 'アイウ',
                'whiteWord' => 'aaa',
            ],
            '全角変換' => [
                'input' => 'ａｂｃｄｅ',
                'ngWord' => 'ABCDE',
                'whiteWord' => 'aaa',
            ],
            '濁点変換' => [
                'input' => 'キチカ゛イ',
                'ngWord' => 'きちがい',
                'whiteWord' => 'aaa',
            ],
            '濁点変換２' => [
                'input' => 'きちか゛い',
                'ngWord' => 'きちがい',
                'whiteWord' => 'aaa',
            ],
        ];
    }
    #[DataProvider('params_NGワードを含む')]    
    public function testValidateNGWord_NGワードを含む(
        string $input,
        string $ngWord,
        string $whiteWord
    )
    {
        MstNgWord::factory()->create(['word' => $ngWord]);
        MstWhiteWord::factory()->create(['word' => $whiteWord]);
        $errorCode = ErrorCode::PLAYER_NAME_USED_NG_WORD;
        $this->expectException(GameException::class);
        $this->expectExceptionCode($errorCode);

        $this->mstNgWordService->validateNGWord($input, $errorCode);
    }

    public static function params_NGワードを含むがWhiteワードに設定があるのでエラーなし()
    {
        return [
            '大文字変換' => [
                'input' => 'ABCDE',
                'ngWord' => 'abc',
                'whiteWord' => 'abcde',
            ],
            'カナ変換' => [
                'input' => 'はくちょう',
                'ngWord' => 'ハクチ',
                'whiteWord' => 'ハクチョウ',
            ],
            '半角カナ変換' => [
                'input' => 'ﾊｸﾁｮｳ',
                'ngWord' => 'ハクチ',
                'whiteWord' => 'ハクチョウ',
            ],
            '全角変換' => [
                'input' => 'ａｂｃｄｅ',
                'ngWord' => 'AB',
                'whiteWord' => 'ABCDE',
            ],
            'NGとWhiteが１文字重複するがWhiteなのでエラーにならない' => [
                'input' => 'ハクチョウ白鳥ちょう',
                'ngWord' => 'ハクチ',
                'whiteWord' => 'チョウ',
            ],
        ];
    }

    #[DataProvider('params_NGワードを含むがWhiteワードに設定があるのでエラーなし')]
    public function testValidateNGWord_NGワードを含むがWhiteワードに設定があるのでエラーなし(
        string $input,
        string $ngWord,
        string $whiteWord
    )
    {
        MstNgWord::factory()->create(['word' => $ngWord]);
        MstWhiteWord::factory()->create(['word' => $whiteWord]);

        $this->mstNgWordService->validateNGWord($input, ErrorCode::PLAYER_NAME_USED_NG_WORD);
        $this->assertTrue(true);
    }

    public static function params_Whiteワードに設定があるがその他文字列でNGがかかってエラー()
    {
        return [
            '大文字変換' => [
                'input' => 'ABCDE',
                'ngWord' => 'ab',
                'ngWord2' => 'cde',
                'whiteWord' => 'AB',
            ],
            'カナ変換' => [
                'input' => 'ただのはくちょう',
                'ngWord' => 'ハクチ',
                'ngWord2' => 'タダノ',
                'whiteWord' => 'ハクチョウ',
            ],
            '半角カナ変換' => [
                'input' => 'ﾀﾀﾞﾉﾊｸﾁｮｳ',
                'ngWord' => 'ハクチ',
                'ngWord2' => 'タダノ',
                'whiteWord' => 'ハクチョウ',
            ],
            '全角変換' => [
                'input' => 'ａｂｃｄｅ',
                'ngWord' => 'ABC',
                'ngWord2' => 'DE',
                'whiteWord' => 'ABC',
            ],
        ];
    }

    #[DataProvider('params_Whiteワードに設定があるがその他文字列でNGがかかってエラー')]
    public function testValidateNGWord_whiteワードに設定があるがその他文字列でNGがかかってエラー(
        string $input,
        string $ngWord,
        string $ngWord2,
        string $whiteWord
    )
    {
        MstNgWord::factory()->create(['word' => $ngWord]);
        MstNgWord::factory()->create(['word' => $ngWord2]);
        MstWhiteWord::factory()->create(['word' => $whiteWord]);
        $errorCode = ErrorCode::PLAYER_NAME_USED_NG_WORD;
        $this->expectException(GameException::class);
        $this->expectExceptionCode($errorCode);

        $this->mstNgWordService->validateNGWord($input, $errorCode);
    }
}

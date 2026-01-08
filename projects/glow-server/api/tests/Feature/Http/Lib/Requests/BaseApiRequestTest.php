<?php

declare(strict_types=1);

namespace Feature\Http\Lib\Requests;

use App\Http\Lib\Requests\BaseApiRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Tests\TestCase;

class BaseApiRequestTest extends TestCase
{
    /**
     * テスト前のapp()['request']の値
     * テスト中に作成したものに置き換えるため、テスト後に戻すために保持
     *
     * @var Request
     */
    private $appRequest;

    protected function setUp(): void
    {
        parent::setUp();

        // テスト前のapp()['request']の値を保持
        $this->appRequest = app()['request'];
    }

    protected function tearDown(): void
    {
        // テスト後にapp()['request']を元に戻す
        app()['request'] = $this->appRequest;

        parent::tearDown();
    }

    /**
     * @test
     */
    public function getter_リクエストから値を取得()
    {
        // Setup
        // テスト用のリクエストを作成
        $symfonyRequest = symfonyRequest::create(
            uri: 'http://localhost',
            method: 'POST',
            parameters: ['name' => 'test'],
        );
        app()['request'] = Request::createFromBase($symfonyRequest);
        $request = $this->app->make(BaseApiRequestTestSample::class);

        // Exercise
        // テスト用のデータを取得
        $name = $request->getName();

        // Verify
        $this->assertEquals('test', $name);
    }

    /**
     * @test
     */
    public function getter_リクエストのバリデーション()
    {
        // Setup
        // テスト用のリクエストを作成
        $symfonyRequest = symfonyRequest::create(
            uri: 'http://localhost',
            method: 'POST',
            parameters: ['id' => '1'],
        );
        app()['request'] = Request::createFromBase($symfonyRequest);

        // Verify
        // リクエストを作った時点でバリデーションが実行される
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->app->make(BaseApiRequestTestSample::class);
    }

    /**
     * @test
     */
    public function getter_プリミティブ型の取得()
    {
        // Setup
        // テスト用のリクエストを作成
        $symfonyRequest = symfonyRequest::create(
            uri: 'http://localhost',
            method: 'POST',
            parameters: [
                'name' => 'test',
                'intValue' => '1',
                'floatValue' => '1.1',
                'boolValue' => 'true',
            ],
        );
        app()['request'] = Request::createFromBase($symfonyRequest);
        $request = $this->app->make(BaseApiRequestTestSample::class);

        // Exercise
        // テスト用のデータを取得
        $intValue = $request->getIntValue();
        $floatValue = $request->getFloatValue();
        $boolValue = $request->getBoolValue();

        // Verify
        $this->assertEquals(1, $intValue);
        $this->assertEquals(1.1, $floatValue);
        $this->assertEquals(true, $boolValue);
    }

    public static function getterNullDataProvider(): array
    {
        return [
            'null' => [null, null],
            'null文字' => ['null', 'null'],
            '空文字' => ['', ''],
        ];
    }

    /**
     * @test
     * @dataProvider getterNullDataProvider
     */
    public function getter_nullの取得($value, $expected)
    {
        // Setup
        // テスト用のリクエストを作成
        $symfonyRequest = symfonyRequest::create(
            uri: 'http://localhost',
            method: 'POST',
            parameters: ['name' => 'test', 'stringNullable' => $value],
        );
        app()['request'] = Request::createFromBase($symfonyRequest);
        $request = $this->app->make(BaseApiRequestTestSample::class);

        // Exercise
        // テスト用のデータを取得
        $actual = $request->getStringNullable();

        // Verify
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function getter_配列の取得()
    {
        // Setup
        // テスト用のリクエストを作成
        $symfonyRequest = symfonyRequest::create(
            uri: 'http://localhost',
            method: 'POST',
            parameters: [
                'name' => 'test',
                'stringArray' => ['test1', 'test2'],
            ],
        );
        app()['request'] = Request::createFromBase($symfonyRequest);
        $request = $this->app->make(BaseApiRequestTestSample::class);

        // Exercise
        // テスト用のデータを取得
        $stringArray = $request->getStringArray();

        // Verify
        $this->assertEquals(\collect(['test1', 'test2']), $stringArray);
    }

    /**
     * @test
     */
    public function getter_enumの取得()
    {
        // Setup
        // テスト用のリクエストを作成
        $symfonyRequest = symfonyRequest::create(
            uri: 'http://localhost',
            method: 'POST',
            parameters: ['name' => 'test', 'type' => 'Sample1'],
        );
        app()['request'] = Request::createFromBase($symfonyRequest);
        $request = $this->app->make(BaseApiRequestTestSample::class);

        // Exercise
        // テスト用のデータを取得
        $type = $request->getType();

        // Verify
        $this->assertEquals(BaseApiRequestTestEnum::Sample1, $type);
    }

    /**
     * @test
     */
    public function getter_null許可されたstring配列取得()
    {
        // Setup
        // テスト用のリクエストを作成
        $symfonyRequest = symfonyRequest::create(
            uri: 'http://localhost',
            method: 'POST',
            parameters: ['name' => 'test', 'stringNullArrayValue' => ['1', '2', null, null, '5']],
        );
        app()['request'] = Request::createFromBase($symfonyRequest);
        $request = $this->app->make(BaseApiRequestTestSample::class);

        // Exercise
        // テスト用のデータを取得
        $stringNullArrayValue = $request->getStringNullArrayValue();

        // Verify
        $this->assertEquals(collect(['1', '2', null, null, '5']), $stringNullArrayValue);
    }
}

/**
 * このユニットテストで使用するクラス
 */
class BaseApiRequestTestSample extends BaseApiRequest
{
    protected static $casts = [
        'name' => 'string',
        'type' => '\Feature\Http\Lib\Requests\BaseApiRequestTestEnum',
        'intValue' => 'int',
        'floatValue' => 'float',
        'boolValue' => 'bool',
        'stringArray' => 'string[]',
        'stringNullable' => 'string|null',
        'stringNullArrayValue' => 'string|null[]',
    ];

    protected static $rules = [
        'name' => 'required',
    ];
}

/**
 * このユニットテスト用のテストクラス
 */
enum BaseApiRequestTestEnum: string
{
    case Sample1 = 'Sample1';
    case Sample2 = 'Sample2';
}

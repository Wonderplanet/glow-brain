<?php

namespace Feature\Http\Middleware;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Constants\System;
use App\Domain\Common\Enums\Language;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Models\MstApiAction;
use App\Http\Middleware\MasterVersionCheck;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Tests\TestCase;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterReleaseVersion;

class MasterVersionCheckTest extends TestCase
{
    private MasterVersionCheck $masterVersionCheck;

    public function setUp(): void
    {
        parent::setUp();
        $this->masterVersionCheck = app()->make(MasterVersionCheck::class);
    }

    /**
     * Requestをモックするヘルパーメソッド
     *
     * @param string $path
     * @param string|null $mstHash
     * @param string|null $oprHash
     * @param string|null $mstI18nHash
     * @param string|null $oprI18nHash
     * @param string|null $clientVersion
     * @param string $language
     * @return MockInterface|Request
     */
    private function createMockedRequest(
        string $path,
        ?string $mstHash = null,
        ?string $oprHash = null,
        ?string $mstI18nHash = null,
        ?string $oprI18nHash = null,
        ?string $clientVersion = null,
        string $language = Language::Ja->value,
    ) {
        return $this->mock(Request::class, function (MockInterface $mock) use (
            $path, $mstHash, $oprHash, $mstI18nHash, $oprI18nHash, $clientVersion, $language
        ) {
            $mock->shouldReceive('header')
                ->with(System::HEADER_MASTER_HASH)
                ->andReturn($mstHash);
            $mock->shouldReceive('header')
                ->with(System::HEADER_OPERATION_HASH)
                ->andReturn($oprHash);
            $mock->shouldReceive('header')
                ->with(System::HEADER_MASTER_I18N_HASH)
                ->andReturn($mstI18nHash);
            $mock->shouldReceive('header')
                ->with(System::HEADER_OPERATION_I18N_HASH)
                ->andReturn($oprI18nHash);
            $mock->shouldReceive('header')
                ->with(System::CLIENT_VERSION)
                ->andReturn($clientVersion);
            $mock->shouldReceive('header')
                ->with(System::HEADER_LANGUAGE)
                ->andReturn($language);
            $mock->shouldReceive('path')
                ->andReturn($path);
        });
    }


    public function test_handle_有効なマスターバージョンの場合は通過する()
    {
        // SetUp
        $clientVersion = '1.0.0';
        $mstHash = 'mstHash123';
        $oprHash = 'oprHash123';
        $mstI18nHash = 'mstI18nHash123';
        $oprI18nHash = 'oprI18nHash123';
        $response = 'hoge';
        $path = '/api/fuga';
        $targetReleaseVersionId = '1';
        $this->fixTime('2023-01-15 12:00:00');

        MngMasterRelease::factory()->create([
            'enabled' => true,
            'target_release_version_id' => $targetReleaseVersionId,
            'client_compatibility_version' => $clientVersion,
            'start_at' => '2023-01-10 00:00:00',
        ]);

        $data = [
            'id' => $targetReleaseVersionId,
            'master_schema_version' => $clientVersion,
            'data_hash' => 'dataHash123',
            'client_mst_data_hash' => $mstHash,
            'client_opr_data_hash' => $oprHash,
        ];

        if ($mstI18nHash !== null) {
            $data['client_mst_data_i18n_ja_hash'] = $mstI18nHash;
        }

        if ($oprI18nHash !== null) {
            $data['client_opr_data_i18n_ja_hash'] = $oprI18nHash;
        }

        MngMasterReleaseVersion::factory()->create($data);

        $mockedRequest = $this->createMockedRequest($path, $mstHash, $oprHash, $mstI18nHash, $oprI18nHash, $clientVersion);
        $next = fn() => $response;

        // Exercise
        $result = $this->masterVersionCheck->handle($mockedRequest, $next);

        // Verify
        $this->assertEquals($response, $result);
    }

    public function test_handle_無効なマスターバージョンの場合は、REQUIRE_RESOURCE_UPDATEの例外が発生する()
    {
        // SetUp
        $clientVersion = '1.0.0';
        $requestMstHash = 'wrongMstHash';
        $correctMstHash = 'correctMstHash';
        $oprHash = 'oprHash123';
        $response = 'hoge';
        $path = '/api/fuga';
        $targetReleaseVersionId = '1';
        $this->fixTime('2023-01-15 12:00:00');

        MngMasterRelease::factory()->create([
            'enabled' => true,
            'target_release_version_id' => $targetReleaseVersionId,
            'client_compatibility_version' => $clientVersion,
            'start_at' => '2023-01-10 00:00:00',
        ]);

        MngMasterReleaseVersion::factory()->create([
            'id' => $targetReleaseVersionId,
            'master_schema_version' => $clientVersion,
            'data_hash' => 'dataHash123',
            'client_mst_data_hash' => $correctMstHash,
            'client_opr_data_hash' => $oprHash,
        ]);

        $mockedRequest = $this->createMockedRequest($path, $requestMstHash, $oprHash, null, null, $clientVersion);
        $next = fn() => $response;

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::REQUIRE_RESOURCE_UPDATE);

        // Exercise
        $this->masterVersionCheck->handle($mockedRequest, $next);

        // Verify
    }

    public function test_handle_マスターバージョンのヘッダーが存在しない場合は通過する()
    {
        // SetUp
        $response = 'hoge';

        /** @var Request */
        $mockedRequest = $this->mock(Request::class, function (MockInterface $mock) {
            $mock->shouldReceive('header');
        });
        $next = fn() => $response;

        // Exercise
        $result = $this->masterVersionCheck->handle($mockedRequest, $next);

        // Verify
        $this->assertEquals($response, $result);
    }

    public function test_handle_配信中のマスターリリース情報がない場合はエラーになる()
    {
        $this->expectException(GameException::class);
        $this->expectExceptionMessage('Wp-Master-Release: Not Found Apply Release');
        $this->expectExceptionCode(ErrorCode::NOT_FOUND_APPLY_MASTER_RELEASE);

        // SetUp
        $clientVersion = '1.0.0';
        $mstHash = 'hash1';
        $oprHash = 'hash4';
        $mstI18nHash = 'hash5';
        $oprI18nHash = 'hash6';
        $response = 'hoge';
        $path = '/api/fuga';

        $mockedRequest = $this->createMockedRequest($path, $mstHash, $oprHash, $mstI18nHash, $oprI18nHash, $clientVersion);
        $next = fn() => $response;

        // Exercise
        $this->masterVersionCheck->handle($mockedRequest, $next);
    }

    public function test_handle_クライアントバージョンと互換性のあるマスターリリース情報がない場合はエラーになる()
    {
        $this->expectException(GameException::class);
        $this->expectExceptionMessage('Wp-Master-Release: Incompatible Client Version: 0.0.9');
        $this->expectExceptionCode(ErrorCode::INCOMPATIBLE_MASTER_DATA_FROM_CLIENT_VERSION);

        // SetUp
        $clientVersion = '0.0.9';
        $mstHash = 'hash1';
        $oprHash = 'hash4';
        $mstI18nHash = 'hash5';
        $oprI18nHash = 'hash6';
        $response = 'hoge';
        $path = '/api/fuga';
        $this->fixTime('2023-01-15 12:00:00');

        MngMasterRelease::factory()
            ->create([
                'release_key' => '202301',
                'client_compatibility_version' => '1.0.0',
                'enabled' => true,
                'target_release_version_id' => 'version_1',
                'start_at' => '2023-01-10 00:00:00',
            ]);
        MngMasterReleaseVersion::factory()
            ->create([
                'id' => 'version_1',
                'release_key' => '202301',
                'git_revision' => 'test1',
                'master_schema_version' => '1.0.0',
                'data_hash' => 'dataHash123',
                'server_db_hash' => 'serverDbHash',
                'client_mst_data_hash' => 'mst1',
                'client_opr_data_hash' => 'opr1',
            ]);

        $mockedRequest = $this->createMockedRequest($path, $mstHash, $oprHash, $mstI18nHash, $oprI18nHash, $clientVersion);
        $next = fn() => $response;

        // Exercise
        $this->masterVersionCheck->handle($mockedRequest, $next);

        // Verify
    }

    public function test_handle_更新チェックをskipするパスの場合は通過する()
    {
        // SetUp
        $clientVersion = '1.0.0';
        $mstHash = 'mstHash';
        $oprHash = 'oprHash';
        $mstI18nHash = 'mstI18nHash';
        $oprI18nHash = 'oprI18nHash';
        $response = 'hoge';
        $path = array_key_first(System::MASTER_CHECK_THROUGH_API);

        $mockedRequest = $this->createMockedRequest($path, $mstHash, $oprHash, $mstI18nHash, $oprI18nHash, $clientVersion);
        $next = fn() => $response;

        // Exercise
        $result = $this->masterVersionCheck->handle($mockedRequest, $next);

        // Verify
        $this->assertEquals($response, $result);
    }

    public function test_handle_異なる時間でも同じキャッシュを使ってフィルタリングしたデータを使ってチェックできている()
    {
        // SetUp
        $currentClientVersion = '1.0.0';
        $futureClientVersion = '2.0.0';
        $mstHash1 = 'mstHash1';
        $mstHash2 = 'mstHash2';
        $oprHash = 'oprHash';
        $mstI18nHash = 'mstI18nHash';
        $oprI18nHash = 'oprI18nHash';
        $path = '/api/fuga';
        $response = 'hoge';

        // 現在有効なデータを作成（現在のclientVersionに対応）
        MngMasterRelease::factory()->create([
            'release_key' => '202301',
            'enabled' => true,
            'target_release_version_id' => 'version_001',
            'client_compatibility_version' => $currentClientVersion,
            'start_at' => '2023-01-10 00:00:00',
        ]);

        MngMasterReleaseVersion::factory()->create([
            'id' => 'version_001',
            'release_key' => '202301',
            'master_schema_version' => $currentClientVersion,
            'data_hash' => 'dataHash1',
            'client_mst_data_hash' => $mstHash1,
            'client_opr_data_hash' => $oprHash,
            'client_mst_data_i18n_ja_hash' => $mstI18nHash,
            'client_opr_data_i18n_ja_hash' => $oprI18nHash,
        ]);

        // 未来のデータを作成（未来のclientVersionに対応）
        MngMasterRelease::factory()->create([
            'release_key' => '202302',
            'enabled' => true,
            'target_release_version_id' => 'version_002',
            'client_compatibility_version' => $futureClientVersion,
            'start_at' => '2023-01-20 00:00:00',
        ]);

        MngMasterReleaseVersion::factory()->create([
            'id' => 'version_002',
            'release_key' => '202302',
            'master_schema_version' => $futureClientVersion,
            'data_hash' => 'dataHash2',
            'client_mst_data_hash' => $mstHash2,
            'client_opr_data_hash' => $oprHash,
            'client_mst_data_i18n_ja_hash' => $mstI18nHash,
            'client_opr_data_i18n_ja_hash' => $oprI18nHash,
        ]);

        $next = fn() => $response;

        // Exercise 1: 現在時点 - 現在clientVersionで通ることを確認
        $this->fixTime('2023-01-15 12:00:00');
        $mockedRequest1 = $this->createMockedRequest($path, $mstHash1, $oprHash, $mstI18nHash, $oprI18nHash, $currentClientVersion);

        $result1 = $this->masterVersionCheck->handle($mockedRequest1, $next);

        // Verify 1
        $this->assertEquals($response, $result1);

        // Exercise 2: 未来時点 - 未来clientVersionで通ることを確認
        $this->fixTime('2023-01-25 12:00:00');
        $mockedRequest2 = $this->createMockedRequest($path, $mstHash2, $oprHash, $mstI18nHash, $oprI18nHash, $futureClientVersion);

        $result2 = $this->masterVersionCheck->handle($mockedRequest2, $next);

        // Verify 2
        $this->assertEquals($response, $result2);

        // Exercise 3: 現在時点 - 未来clientVersionで通らないことを確認
        $this->fixTime('2023-01-15 12:00:00');
        $mockedRequest3 = $this->createMockedRequest($path, $mstHash2, $oprHash, $mstI18nHash, $oprI18nHash, $futureClientVersion);

        $this->expectException(GameException::class);
        $this->expectExceptionCode(ErrorCode::REQUIRE_RESOURCE_UPDATE);

        $this->masterVersionCheck->handle($mockedRequest3, $next);
    }
}

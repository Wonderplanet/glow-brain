<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators;

/**
 * マスターデータインポートv2管理ツール用
 *
 * MEMO v1と実装が変わる可能性を考慮し別クラスで作成
 */
class GoogleDriveOperator
{
    private $client;
    private $service;

    public function __construct(string $keyPath)
    {
        if (!file_exists($keyPath)) {
            // `client_credentials.json`のパスが存在しない場合はエラーになるため初期化しない
            return;
        }
        $this->client = new \Google_Client();
        $this->client->setAuthConfig($keyPath);
        $this->client->setApplicationName("Test"); // 適当な名前でOK
        $this->client->addScope(\Google\Service\Drive::DRIVE);
        $this->service = new \Google\Service\Drive($this->client);
    }

    /**
     * ドライブ内のファイルのIDとファイル名のリストを返す
     *
     * @return array
     * @throws \Google\Service\Exception
     */
    public function getFileList(): array
    {
        if ($this->service == null) {
            return [];
        }
        $response = $this->service->files->listFiles([
            'q' => "'" . config('wp_master_asset_release_admin.googleSpreadSheetDirId') . "' in parents ".
                " and (name contains '" . config('wp_master_asset_release_admin.googleSpreadSheetNamePrefix') . "'".
                " or name contains '" . config('wp_master_asset_release_admin.googleSpreadSheetOprNamePrefix') . "')".
                " and mimeType='application/vnd.google-apps.spreadsheet' and trashed=false",
            'includeItemsFromAllDrives' => true,
            'supportsTeamDrives' => true,
            'orderBy' => 'name',
        ]);

        return array_map(static function ($value): array {
            return [
                'id' => $value["id"],
                'fileName' => $value["name"],
                ];
            }, $response->getFiles());
    }
}

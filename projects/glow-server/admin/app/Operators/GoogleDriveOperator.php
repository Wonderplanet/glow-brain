<?php
namespace App\Operators;
use App\Constants\GoogleDriveMimeType;

class GoogleDriveOperator
{
    private $client;
    private $service;

    public function __construct(string $keyPath)
    {
        $this->client = new \Google_Client();
        $this->client->setAuthConfig($keyPath);
        $this->client->setApplicationName("Test"); // 適当な名前でOK
        $this->client->addScope(\Google\Service\Drive::DRIVE);
        $this->service = new \Google\Service\Drive($this->client);
    }

    // ドライブ内のファイルのIDリストを返す
    public function getFileIdList(GoogleDriveMimeType $mimeType): array
    {
        $response = $this->service->files->listFiles([
            'q' => "'" . config('admin.googleSpreadSheetDirId') . "' in parents ".
                " and (name contains '" . config('admin.googleSpreadSheetNamePrefix') . "'".
                " or name contains '" . config('admin.googleSpreadSheetOprNamePrefix') . "')".
                " and mimeType='application/vnd.google-apps.spreadsheet' and trashed=false",
            'includeItemsFromAllDrives' => true,
            'supportsTeamDrives' => true,
            'orderBy' => 'name',
        ]);
        return array_map(fn($value): string => $value["id"], $response->getFiles());
    }
}

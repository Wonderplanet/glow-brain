<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Operators;

use Google\Service\Sheets;
use Google\Service\Sheets\Sheet;

class GoogleSpreadSheetOperator
{
    private $client;
    private $service;

    public function __construct(string $credentialPath)
    {
        if (!file_exists($credentialPath)) {
            // `client_credentials.json`のパスが存在しない場合はエラーになるため初期化しない
            return;
        }
        $this->client = new \Google_Client();
        $this->client->setAuthConfig($credentialPath);
        $this->client->setApplicationName("Test"); // 適当な名前でOK
        $this->client->addScope(Sheets::SPREADSHEETS);
        $this->service = new Sheets($this->client);
    }

    /**
     * シートの一覧を取得する
     * https://developers.google.com/sheets/api/reference/rest/v4/spreadsheets/get?hl=ja
     * @param string $spreadSheetId
     * @return Sheet[]
     */
    public function getSheets(string $spreadSheetId): array
    {
        if ($this->service == null) {
            return [];
        }
        $spreadsheet = $this->service->spreadsheets->get($spreadSheetId);
        return $spreadsheet->getSheets();
    }

    // シートのタイトル名のリストを返す
    // https://developers.google.com/sheets/api/reference/rest/v4/spreadsheets/get?hl=ja
    public function getSheetTitleList(string $spreadSheetId): array
    {
        return array_map(fn($value) => $value["properties"]["title"], $this->getSheets($spreadSheetId));
    }

    // シートID内のシート名が指すデータを全て取得する
    // https://developers.google.com/sheets/api/reference/rest/v4/spreadsheets.values/get?hl=ja
    public function getSheetValues(string $spreadSheetId, string $sheetName): array
    {
        if ($this->service == null) {
            return [];
        }
        // シート内の全セルを参照
        // dateTimeRenderOptionにFORMATTED_STRINGを設定することで、日時データがシリアル値ではなく入力された文字で渡されます
        // 例：シート内でカレンダー入力されたY/m/dの日付を、シリアル値ではなくY/m/dの文字列として取得する
        // 詳細はこちらのリンクを参照：https://developers.google.com/sheets/api/reference/rest/v4/DateTimeRenderOption?hl=ja
        $response = $this->service->spreadsheets_values->get($spreadSheetId, $sheetName, ['valueRenderOption' => 'UNFORMATTED_VALUE', 'dateTimeRenderOption' => 'FORMATTED_STRING']);
        return $response->getValues() ?? [];
    }

    // TODO: セルのフォーマットを取得して復元できるように
}

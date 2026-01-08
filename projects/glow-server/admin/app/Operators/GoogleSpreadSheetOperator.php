<?php

namespace App\Operators;

use Google\Service\Sheets;
use Google\Service\Sheets\Sheet;

class GoogleSpreadSheetOperator
{
    private $client;
    private $service;

    public function __construct(string $credentialPath)
    {
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
        // シート内の全セルを参照
        $response = $this->service->spreadsheets_values->get($spreadSheetId, $sheetName, ['valueRenderOption' => 'UNFORMATTED_VALUE']);
        return $response->getValues() ?? [];
    }

    // TODO: セルのフォーマットを取得して復元できるように
}

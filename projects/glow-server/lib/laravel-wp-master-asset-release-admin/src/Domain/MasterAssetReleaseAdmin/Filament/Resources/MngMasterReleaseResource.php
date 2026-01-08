<?php

namespace WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources;

use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use WonderPlanet\Domain\Admin\Filament\Tables\Columns\DateTimeColumn;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Constants\MasterAssetReleaseConstants;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Filament\Resources\MngMasterReleaseResource\Pages;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Models\Mng\MngMasterRelease;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Services\MngMasterReleaseService;
use WonderPlanet\Domain\MasterAssetReleaseAdmin\Utils\ClientCompatibilityVersionUtility;

/**
 * マスターリリース管理画面リソースクラス
 */
class MngMasterReleaseResource extends Resource
{
    protected static ?string $model = MngMasterRelease::class;
    protected static ?string $navigationIcon = 'heroicon-m-list-bullet';
    protected static ?int $navigationSort = -999;
    protected static ?string $navigationGroup = 'v2 マスター・アセット管理';
    protected static ?string $navigationLabel = 'マスターデータ配信管理';
    protected static ?string $modelLabel = 'マスターデータ配信管理ダッシュボード';

    public static function form(Form $form): Form
    {
        // 登録済みのリリース情報の中から最新のものを取得
        /** @var MngMasterReleaseService $service */
        $service = app()->make(MngMasterReleaseService::class);
        $maxReleaseKeyMngMasterRelease = $service->getMngMasterReleasesByApplyOrPending()
            ->first();
        $maxReleaseKey = is_null($maxReleaseKeyMngMasterRelease)
            ? 0
            : $maxReleaseKeyMngMasterRelease->release_key;
        $maxClientVersion = is_null($maxReleaseKeyMngMasterRelease)
            ? null
            : $maxReleaseKeyMngMasterRelease->client_compatibility_version;

        // クライアント互換性バージョンのバリデーションコールバックメソッド(ここでは実行してない)
        $validationClientVersion = ClientCompatibilityVersionUtility::makeValidateClientCompatibilityVersion($maxClientVersion);

        return $form
            ->schema([
                Forms\Components\TextInput::make('release_key')
                    ->required()
                    ->placeholder('例: 202409260')
                    ->label('release_key')
                    ->unique()
                    ->numeric()
                    ->maxValue(MasterAssetReleaseConstants::MAX_RELEASE_KEY)
                    ->minValue($maxReleaseKey)
                    ->validationMessages([
                        'unique' => 'すでに登録済みのrelease_keyです',
                    ]),
                Forms\Components\TextInput::make('client_compatibility_version')
                    ->required()
                    ->placeholder('例: 0.0.0')
                    ->label('クライアント互換性バージョン')
                    // 数字と.のみ許可するバリデーション
                    ->rules([
                        'regex:/^\d+\.\d+\.\d+$/',
                        fn () => function ($attribute, $value, Closure $fail) use ($validationClientVersion) {
                            // クライアント互換性バージョンのバリデーションを実行
                            $validationClientVersion($attribute, $value, $fail);
                        },
                    ])
                    ->validationMessages([
                        'regex' => '「数字.数字.数字」の形式で入力してください',
                    ]),
                Forms\Components\Textarea::make('description')
                    ->label('メモ')
                    ->placeholder('メモしたい内容があれば入力してください')
                    ->columnSpan(2)
                    ->rows(3),
                Forms\Components\DateTimePicker::make('start_at')
                    ->label('開始日時')
                    ->required()
                    ->reactive()
                    ->format('Y-m-d H:i:s'),
            ]);
    }

    public static function table(Table $table): Table
    {
        /** @var MngMasterReleaseService $service */
        $service = app()->make(MngMasterReleaseService::class);

        // 配信中ステータスのMngMasterReleaseを取得
        $mngMasterReleasesByApply = $service->getMngMasterReleasesByApply();
        // 最新の配信中ステータスのMngMasterReleaseを取得
        $latestReleasedMngMasterRelease = $service->getLatestReleasedMngMasterRelease();

        // last import at(created_at)を取得する為にadm_master_import_historiesのデータを取得する
        // MEMO データ量が多くなると画面が重くなる可能性があるので、その場合は別途取得する方法を検討する
        $admMasterImportHistories = $service->getLastImportAtMap();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('enabled')
                    ->label('Status')
                    ->formatStateUsing(function ($record) use ($mngMasterReleasesByApply, $latestReleasedMngMasterRelease) {
                        // デフォルトで「配信終了」を設定
                        $status = new HtmlString("<span style='color: gray'>配信終了</span>");
                        if ($record->enabled && !is_null($record->target_release_version_id)) {
                            if ($mngMasterReleasesByApply->isNotEmpty()) {
                                $applyReleaseKeys = $mngMasterReleasesByApply->pluck('release_key')->toArray();
                                if (in_array($record->release_key, $applyReleaseKeys, true)) {
                                    // enabledフラグがtrue かつ target_release_version_idが設定されているレコードで、配信中リリースキーと一致するものを「配信中」で表示
                                    $status = new HtmlString("<span style='color: deeppink'>配信中</span>");
                                }
                            }
                        }
                        if (!$record->enabled || is_null($record->target_release_version_id)) {
                            // enabledフラグがfalse または target_release_version_idが設定されていない場合
                            // 最新の配信中データがnullの場合は$recordのデータが最新なので「配信準備中」と表示
                            // 最新の配信中データがある場合、$recordのreleaseKeyの方がより最新なら「配信準備中」で表示
                            if (is_null($latestReleasedMngMasterRelease) || ($record->release_key > $latestReleasedMngMasterRelease->release_key)) {
                                $status = new HtmlString("<span style='color: darkolivegreen'>配信準備中</span>");
                            }
                        }
                        return $status;
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('release_key')
                    ->label('Release Key')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_compatibility_version')
                    ->label('クライアント互換性バージョン')
                    ->searchable()
                    ->prefix(function ($record) {
                        // 値が未入力なら接頭辞を非表示にする
                        return blank($record->client_compatibility_version) ? null : '>= ';
                    })
                    ->state(function ($record) {
                        // 値が未入力ならメッセージを表示
                        return $record->client_compatibility_version ?? '未入力です！';
                    })
                    ->icon(function ($record) {
                        // 値が未入力なら警告アイコンを表示
                        return is_null($record->client_compatibility_version) ? 'heroicon-o-exclamation-circle' : '';
                    })
                    ->color(function ($record) {
                        // 値が未入力ならアイコンと文字を赤色に表示
                        return is_null($record->client_compatibility_version) ? 'danger' : '';
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('メモ欄')
                    ->extraAttributes(['style' => 'width:16rem'])
                    ->wrap(),
                DateTimeColumn::make('start_at')
                    ->label('開始日時')
                    ->sortable(),
                Tables\Columns\TextColumn::make('mngMasterReleaseVersion.git_revision')
                    ->label('git revision'),
                Tables\Columns\TextColumn::make('mngMasterReleaseVersion.data_hash')
                    ->label('data hash'),
                DateTimeColumn::make('custom_import_at')
                    ->label('last import at')
                    ->getStateUsing(function ($record) use ($admMasterImportHistories) {
                        // mng_master_releases.target_release_version_idをもとに履歴テーブルからインポート最新日を取得する
                        if (is_null($record->target_release_version_id) || empty($admMasterImportHistories)) {
                            // recordのtarget_release_version_idがnull または $admMasterImportHistories空だった場合は何も表示しない
                            return null;
                        }
                        // 該当target_release_version_idのcreated_atがあれば日付を、なければnullを返す
                        return $admMasterImportHistories[$record->target_release_version_id] ?? null;
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('custom_icon')
                    ->label('')
                    ->getStateUsing(function ($record) {
                        // 警告マークを表示する
                        // TODO 現在想定している条件は下記だが、関連する処理が未実装のため
                        //  target_release_version_idがnullだったら警告を出すように仮実装している
                        //  関連する処理が実装されたら条件を修正する
                        //   1.mng_master_releases.target_release_version_idに設定されているmng_master_release_versions.created_atよりデータが存在する場合
                        //     ※mng系にはcreated_atを追加しないというコード規約があったと思うが、ここも見直すエピックがあるらしいので仕様としてはこのままで
                        //   2.mng_master_releases.target_release_version_idに紐づくadm_master_release_version_statusesのocarina_validated_statusがNGだった場合
                        //   3.[優先度低]現在のマスタースキームバージョンの形式とtarget_release_version_idに指定されているmaster_schema_versionが異なる場合
                        if (is_null($record->target_release_version_id)) {
                            // 表示する条件の場合は特定の文字列を返す
                            return 'alert';
                        }
                        // 条件に該当しない場合はnullを返す
                        return null;
                    })
                    ->icon(fn (string $state) => match ($state) {
                        // 表示するアイコン画像を指定(キーはgetStateUsingで返る文字列)
                        'alert' => 'heroicon-o-exclamation-circle',
                    })
                    ->color(fn (string $state) => match ($state) {
                        // 表示アイコンの色を指定(キーはgetStateUsingで返る文字列)
                        'alert' => 'danger',
                    }),
            ])
            ->heading('※リリース設定をした最新のrelease_key2つまでが配信中ステータスになります')
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('詳細'),
                // TODO データ一覧ボタンを追加
            ])
            ->defaultSort('release_key', 'desc')
            ->searchable(false)
            // 行のリンク化をさせないように制御
            ->recordUrl(null);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMngMasterReleases::route('/'),
            'create' => Pages\CreateMngMasterRelease::route('/create'),
            'view' => Pages\ViewMngMasterRelease::route('/{record}'),
            // TODO データ一覧のリンクを追加
        ];
    }
}

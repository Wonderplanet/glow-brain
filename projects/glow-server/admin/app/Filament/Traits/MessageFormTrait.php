<?php

namespace App\Filament\Traits;

use App\Domain\Resource\Enums\RewardType;
use App\Facades\Promotion;
use App\Models\Adm\Enums\AdmMessageAccountCreatedTypes;
use App\Models\Adm\Enums\AdmMessageCreateStatuses;
use App\Models\Adm\Enums\AdmMessageTargetIdInputTypes;
use App\Models\Adm\Enums\AdmMessageTargetTypes;
use App\Models\Usr\UsrUserProfile;
use App\Traits\MngCacheDeleteTrait;
use App\Traits\RewardInfoGetTrait;
use Carbon\CarbonImmutable;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * メッセージ配布登録/編集で共通して利用する処理を置いたtrait
 */
trait MessageFormTrait
{
    use RewardInfoGetTrait;
    use MngCacheDeleteTrait;

    public bool $isAlDistribution;

    // 入力内容を保持する変数群
    public string $titleJp = '';
    public string $checkCreatedAccount = AdmMessageAccountCreatedTypes::Unset->value;
    public string $checkPlayerType = '';
    public string $playerId = '';
    public array $targetIdFiles = [];
    public string $targetIdInputType = AdmMessageTargetIdInputTypes::All->value;
    public string $accountCreatedStartAt = '';
    public string $accountCreatedEndAt = '';
    public string $distributionStartDate = '';
    public string $distributionEndDate = '';
    public string $checkAddExpiredDays = 'sevenDays';
    public string $inputAddExpiredDays = '';
    public string $bodyJp = '';
    public array $itemSelected = [];
    public bool $isInputtable; // 入力可能フラグ。新規作成またはcreate_statusが下書き状態(Editing)はtrue、それ以外のcreate_statusではfalse
    public Collection $targetIdCollection;

    private array $tmpFileNames = [];

    /** 下記は編集画面でのみ使用 */
    public int $admMessageDistributionInputId;
    public AdmMessageCreateStatuses $createStatus;
    public CarbonImmutable $nowJst;
    public bool $isRegisteredIndividual = false; // 個別配布登録が実行済みか。trueなら実行済み、falseなら未実行
    public bool $isRegisteredMngMessage = false; // opr関連のデータが登録済みか。trueなら登録済み、falseなら未登録
    public string|null $targetIdsTxt = null;
    public string|null $admPromotionTagId = null; // 昇格タグID

    /**
     * @param string $subTitle
     * @return HtmlString
     */
    private function makeSubTitleString(string $subTitle): HtmlString
    {
        return new HtmlString('<p class="font-bold text-xl">' . $subTitle . '</p>');
    }

    /**
     * マウント処理共通部分
     *
     * @return void
     */
    protected function createMount(): void
    {
        if ($this->isAlDistribution) {
            $this->checkPlayerType = '';
            $this->targetIdFiles = [];
            $this->playerId = '';
            $this->targetIdCollection = collect();
            $this->targetIdsTxt = null;
        } else {
            $this->checkPlayerType = AdmMessageTargetTypes::MyId->value;
            $this->checkCreatedAccount = AdmMessageAccountCreatedTypes::Unset->value;
            $this->accountCreatedStartAt = '';
            $this->accountCreatedEndAt = '';
        }
    }

    /**
     * @param Form $form
     * @return Form
     */
    public function baseForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Fieldset::make('titleBox')
                ->label($this->makeSubTitleString('基本情報'))
                ->schema(array_merge(
                    [
                        Forms\Components\TextInput::make('titleJp')
                            ->required()
                            ->label('配布タイトルJP')
                            ->reactive(),
                    ],
                    $this->isAlDistribution
                        ? [Promotion::getTagSelectForm('admPromotionTagId')]
                        : []
                )),

            Forms\Components\Fieldset::make('distributions')
                ->label($this->makeSubTitleString('配布対象'))
                ->disabled($this->isRegisteredIndividual)
                ->schema([
                    Forms\Components\Hidden::make('isAlDistribution'),
                    Forms\Components\Grid::make()
                        ->columns(1)
                        ->schema([
                            Forms\Components\Grid::make()
                                ->visible(fn (callable $get) => !$get('isAlDistribution'))
                                ->columns(2)
                                ->columnSpan(1)
                                ->schema([
                                    Forms\Components\Radio::make('checkPlayerType')
                                        ->label(new HtmlString('個別配布の場合はMyIdかUserIdを選択して下さい'))
                                        ->options([
                                            AdmMessageTargetTypes::MyId->value => 'MyId(ユーザーに公開されているID)',
                                            AdmMessageTargetTypes::UserId->value => 'UserId(サーバー内部管理用ID)'
                                        ])
                                        ->columns(2)
                                        ->columnSpan(2)
                                        ->reactive()
                                        ->afterStateUpdated(function (callable $set, $state) {
                                            if ($state !== '') {
                                                // アカウント作成日時関連のパラメータをリセット(全体配布でしか使用しないため)
                                                $set('checkCreatedAccount', AdmMessageAccountCreatedTypes::Unset->value);
                                                $set('accountCreatedStartAt', '');
                                                $set('accountCreatedEndAt', '');
                                            }
                                        }),
                                    Forms\Components\TextInput::make('playerId')
                                        ->view(
                                            'forms.components.input-under-line',
                                            [
                                                'placeholder' => 'MyId or UserId',
                                                'addStyle' => 'margin-top: 1.75rem;',
                                                'disabled' => ($this->isInputtable && !$this->isRegisteredIndividual) ? '' : 'disabled', // 状態が入力可能かつ個別配布登録が実行済みでなければ操作可能にする
                                            ]
                                        )
                                        ->afterStateUpdated(function (callable $set, $state) {
                                            // playerIdを入力したら今保持してるtargetIdの情報をリセット
                                            if ($state !== '') {
                                                $this->targetIdCollection = collect();
                                                $this->targetIdsTxt = null;
                                            }
                                        })
                                        ->reactive(),
                                    Forms\Components\FileUpload::make('targetIdFiles')->placeholder('CSV')
                                        ->extraInputAttributes(['style' => 'margin-top: 1.0rem;'])
                                        ->hintIcon('heroicon-o-arrow-up-tray')
                                        ->loadingIndicatorPosition('left')
                                        ->label('')
                                        ->hintAction(
                                        // 登録している個別配布対象IDを別タブで表示
                                            Forms\Components\Actions\Action::make('targetIdDetailLink')
                                                ->label(function () {
                                                    $label = '対象IDを確認';
                                                    $label .= $this->isRegisteredIndividual
                                                        ? '<br/>(登録済みのため変更できません)'
                                                        : '';
                                                    return new HtmlString($label);
                                                })
                                                ->url('target-id-detail', true)
                                                ->link()
                                                ->visible(fn() => !is_null($this->targetIdsTxt)),
                                        )
                                        ->columnSpan(1)
                                        ->storeFiles(false)
                                        ->rules(['file', 'mimes:csv,txt'])
                                        ->acceptedFileTypes(['text/csv', 'text/plain'])
                                        ->reactive()
                                        ->afterStateUpdated(function (callable $set) {
                                            // ファイルがアップロードされたらtargetIdCollectionをリセットする
                                            //  ファイルアップロード->再アップロードした時に正しくセットするため
                                            $this->targetIdCollection = collect([]);
                                            // playerIdの入力内容をリセット
                                            $set('playerId', '');
                                            // 既存のtargetIdsTxtをリセット
                                            $set('targetIdsTxt', null);
                                        })
                                        ->maxSize(2097152) // アップロードファイルサイズ上限値は2GBを指定 上限値はphp.iniのupload_max_filesize、config/livewire.phpのrules.maxの値と合わせる
                                        ->helperText('※最大10万人までの実行可能です')
                                        ->disabled(!$this->isInputtable),
                                ]),
                            Forms\Components\Grid::make()
                                ->visible(fn (callable $get) => $get('isAlDistribution'))
                                ->columns(2)
                                ->columnSpan(1)
                                ->schema([
                                    Forms\Components\Radio::make('checkCreatedAccount')->label('アカウント作成日時')
                                        ->options([
                                            AdmMessageAccountCreatedTypes::Unset->value => '未設定',
                                            AdmMessageAccountCreatedTypes::Started->value => '開始日のみ',
                                            AdmMessageAccountCreatedTypes::Ended->value => '終了日のみ',
                                            AdmMessageAccountCreatedTypes::Both->value => '両方',
                                        ])
                                        ->columns(4)
                                        ->columnSpan(2)
                                        ->reactive()
                                        ->afterStateUpdated(function (callable $set, $state) {
                                            // checkCreatedAccountの状態に応じてアカウント作成/終了日時の値を制御する
                                            // 両方の場合は何もしない(バリデーションに任せる)
                                            if ($state === AdmMessageAccountCreatedTypes::Unset->value) {
                                                // 未設定:アカウント作成/終了日時をリセット
                                                $set('accountCreatedStartAt', '');
                                                $set('accountCreatedEndAt', '');
                                            }
                                            if ($state === AdmMessageAccountCreatedTypes::Started->value) {
                                                // 開始日のみ:アカウント終了日時をリセット
                                                $set('accountCreatedEndAt', '');
                                            }
                                            if ($state === AdmMessageAccountCreatedTypes::Ended->value) {
                                                // 終了日のみ:アカウント開始日時をリセット
                                                $set('accountCreatedStartAt', '');
                                            }
                                        })
                                        ->disabled(fn() => $this->checkPlayerType !== ''), // 個別配布ユーザーが指定されたら操作不可
                                    Forms\Components\DateTimePicker::make('accountCreatedStartAt')
                                        ->label('アカウント作成日時~')
                                        ->reactive()
                                        ->disabled(function (callable $get) {
                                            // 未設定と終了日のみを選択したら入力できないようにする
                                            return in_array(
                                                $get('checkCreatedAccount'),
                                                [AdmMessageAccountCreatedTypes::Unset->value, AdmMessageAccountCreatedTypes::Ended->value],
                                                true
                                            );
                                        })
                                        ->format('Y-m-d H:i:s'),
                                    Forms\Components\DateTimePicker::make('accountCreatedEndAt')
                                        ->label('~アカウント作成日時')
                                        ->reactive()
                                        ->disabled(function (callable $get) {
                                            // 未設定と開始日のみを選択したら入力できないようにする
                                            return in_array(
                                                $get('checkCreatedAccount'),
                                                [AdmMessageAccountCreatedTypes::Unset->value, AdmMessageAccountCreatedTypes::Started->value],
                                                true
                                            );
                                        })
                                        ->format('Y-m-d H:i:s'),
                                ]),
                        ]),
                ]),

            Forms\Components\Fieldset::make('distributionDate')
                ->label($this->makeSubTitleString('配布日時'))
                ->schema([
                    Forms\Components\DateTimePicker::make('distributionStartDate')
                        ->label('配布開始日時')
                        ->required()
                        ->reactive()
                        ->format('Y-m-d H:i:s'),
                    Forms\Components\DateTimePicker::make('distributionEndDate')
                        ->label('配布終了日時')
                        ->required()
                        ->reactive()
                        ->format('Y-m-d H:i:s'),
                    Forms\Components\Grid::make()
                        ->schema([
                            Forms\Components\Radio::make('checkAddExpiredDays')
                                ->label('受取後有効期限日数')
                                ->required()
                                ->options(['sevenDays' => '7日', 'thirtyDays' => '30日', 'otherDays' => 'その他'])
                                ->columns(3)
                                ->reactive()
                                ->afterStateUpdated(function (callable $set, $state) {
                                    if ($state !== 'otherDays') {
                                        // otherDays以外をチェックしたら日数の入力をリセット
                                        $set('inputAddExpiredDays', '');
                                    }
                                }),
                            Forms\Components\TextInput::make('inputAddExpiredDays')->label('')
                                ->placeholder('その他の場合は日数を入力')
                                ->numeric()
                                ->extraAttributes(['style' => 'margin-top: 1.75rem; width: 15rem;']) // 上に1行分の空白を作成、横幅を指定
                                ->disabled(fn(callable $get) => $get('checkAddExpiredDays') !== 'otherDays') // その他以外がチェックされていたら入力できない
                                ->reactive(),
                        ])->columns(2),
                ])->columns(2),

            Forms\Components\Fieldset::make('messageBox')
                ->label($this->makeSubTitleString('本文'))
                ->schema([
                    Forms\Components\Textarea::make('bodyJp')
                        ->required()
                        ->label('本文(jp)')
                        ->reactive(),
                ]),
            Forms\Components\Repeater::make('itemSelected')
                ->columnSpanFull()
                ->label($this->makeSubTitleString('配布アイテム設定'))
                ->schema(self::getSendRewardSchema(
                    $this->getDistributionTypes(),
                    'distributionType',
                    'distributionId',
                    'distributionQuantity'
                ))
                ->columns(4)
                ->addActionLabel('配布アイテム設定へ追加')
                ->reorderableWithButtons(),
        ])->disabled(!$this->isInputtable);
    }

    /**
     * 報酬配布種別のリストを取得
     *
     * @return array<string, string>
     */
    private function getDistributionTypes(): array
    {
        return [
            RewardType::FREE_DIAMOND->value => RewardType::FREE_DIAMOND->label(),
            RewardType::COIN->value => RewardType::COIN->label(),
            RewardType::EXP->value => RewardType::EXP->label(),
            RewardType::STAMINA->value => RewardType::STAMINA->label(),
            RewardType::UNIT->value => RewardType::UNIT->label(),
            RewardType::ITEM->value => RewardType::ITEM->label(),
            RewardType::EMBLEM->value => RewardType::EMBLEM->label(),
        ];
    }

    /**
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function customValidate(): void
    {
        // バリデーションメッセージ設定
        $this->validate(
            messages: [
                'titleJp.required' => 'タイトルは必須です',
                'distributionStartDate.required' => '配布開始日時は必須です',
                'distributionEndDate.required' => '配布終了日時は必須です',
                'bodyJp.required' => '本文は必須です',
            ]
        );

        // 配布開始日時関連のバリデーション
        $this->validate(
            ['distributionEndDate' => 'after:distributionStartDate'],
            ['distributionEndDate.after' => '配布開始日時以前にはできません']
        );

        // アカウント作成日時関連のバリデーション
        if ($this->checkCreatedAccount === AdmMessageAccountCreatedTypes::Started->value) {
            // アカウント作成日時が「開始日のみ」だった場合は作成日時のバリデーションを実行
            $this->validate(
                ['accountCreatedStartAt' => 'required'],
                ['accountCreatedStartAt.required' => '開始日時が未入力です']
            );
        }
        if ($this->checkCreatedAccount === AdmMessageAccountCreatedTypes::Ended->value) {
            // アカウント作成日時が「終了日のみ」だった場合は作成日時のバリデーションを実行
            $this->validate(
                ['accountCreatedEndAt' => 'required'],
                ['accountCreatedEndAt.required' => '終了日時が未入力です']
            );
        }
        if ($this->checkCreatedAccount === AdmMessageAccountCreatedTypes::Both->value) {
            // アカウント作成日時が「両方」だった場合は作成日時のバリデーションを実行
            $this->validate(
                ['accountCreatedStartAt' => 'required', 'accountCreatedEndAt' => 'required'],
                ['accountCreatedStartAt.required' => '開始日時が未入力です', 'accountCreatedEndAt.required' => '終了日時が未入力です']
            );
        }

        foreach ($this->itemSelected as $item) {
            // 配布アイテムを追加した際のバリデーション
            if (is_null($item['distributionType'])) {
                throw ValidationException::withMessages([
                    "itemSelected" => ['アイテムタイプが未設定です'],
                ]);
            }
            if (is_null($item['distributionId'])) {
                switch ($item['distributionType']) {
                    case RewardType::COIN->value:
                    case RewardType::FREE_DIAMOND->value:
                    case RewardType::STAMINA->value:
                    case RewardType::EXP->value:
                        break;
                    default:
                        throw ValidationException::withMessages([
                            "itemSelected" => ['アイテムIDが未設定です'],
                        ]);
                }
            }
            if (($item['distributionQuantity']) <= 0) {
                throw ValidationException::withMessages([
                    "itemSelected" => ['配布個数は1以上の値を入力してください'],
                ]);
            }
        }

        // 個別配布のバリデーション
        $this->validateTargetIdCollection();
    }

    /**
     * 個別配布対象ユーザーのバリデーション
     *
     * @return void
     * @throws ValidationException
     */
    public function validateTargetIdCollection(): void
    {
        if ($this->isAlDistribution) {
            // 全体配布の実行であれば何もしない
            return;
        }

        // 個別配布だが対象ユーザーを指定していなかった
        if ($this->playerId === '' && $this->targetIdCollection->isEmpty()) {
            throw ValidationException::withMessages([
                'targetIdFiles' => ['プレイヤーIDが未入力かcsvファイルが未指定です'],
            ]);
        }

        $idType = $this->checkPlayerType === AdmMessageTargetTypes::MyId->value
            ? 'my_id'
            : 'usr_user_id';

        // ユーザー存在チェック
        $diffs = collect();
        foreach ($this->targetIdCollection->chunk(5000) as $targetIdsColl) {
            $usrUserProfiles = UsrUserProfile::query()
                ->whereIn($idType, $targetIdsColl->toArray())
                ->get($idType);
            $diff = $targetIdsColl->diff($usrUserProfiles->map(fn($row) => $row->$idType)->toArray());
            $diffs = $diffs->merge($diff);
        }

        if ($diffs->isNotEmpty()) {
            Log::info('undefined playerId', [$diffs->toArray()]);
            throw ValidationException::withMessages(['targetIdFiles' => ['存在しないプレイヤーIDが指定されています']]);
        }
    }

    /**
     * 入力フォームから個別入力やファイルアップロードした個別配布対象ユーザーをパラメータにセットする
     *
     * @return void
     */
    public function setTargetIdCollectionFormInput(): void
    {
        $targetIds = collect();

        if (empty($this->targetIdFiles)) {
            // csvファイルがアップロードされてなければ個別入力のユーザーを保存
            // csvファイルがアップロードされている場合は入力欄のIDは無視する
            $targetIds->push($this->playerId);
        }

        /** @var TemporaryUploadedFile $tmpFile */
        foreach ($this->targetIdFiles as $tmpFile) {
            // アップロードファイルからIDを取得
            $filepath = $tmpFile->getRealPath();
            $file = new \SplFileObject($filepath);
            $file->setFlags(\SplFileObject::READ_CSV);

            foreach ($file as $rows) {
                $targetIds = $targetIds->merge($rows);
            }

            // 一時ファイル削除用にファイル名を保存
            $this->tmpFileNames[] = $tmpFile->getFilename();
        }

        // 空文字 or nullと重複IDがあれば除外して登録する
        $this->targetIdCollection = $targetIds->filter(fn($value) => !empty($value))->unique();

        // csvアップロードがされているならCsvで、そうでなければInputで登録
        $this->targetIdInputType = !empty($this->targetIdFiles)
            ? AdmMessageTargetIdInputTypes::Csv->value
            : AdmMessageTargetIdInputTypes::Input->value;
    }

    /**
     * 登録/更新処理用にパラメータを整形する
     *
     * @return array<int, mixed>
     * @throws \JsonException
     */
    public function formattedForParams(): array
    {
        // 受け取り期限加算日数を算出
        $addExpiredDays = match ($this->checkAddExpiredDays) {
            'sevenDays' => 7,
            'thirtyDays' => 30,
            'otherDays' => (int) $this->inputAddExpiredDays,
        };

        // mng_messages登録データ生成
        $startAt = (new CarbonImmutable($this->distributionStartDate));
        $expiredAt = (new CarbonImmutable($this->distributionEndDate));
        $mngMessageData = [
            'start_at' => $startAt,
            'expired_at' => $expiredAt,
            'type' => $this->isAlDistribution ? 'All' : 'Individual',
            'account_created_start_at' => empty($this->accountCreatedStartAt) ? null : (new CarbonImmutable($this->accountCreatedStartAt)),
            'account_created_end_at' => empty($this->accountCreatedEndAt) ? null : (new CarbonImmutable($this->accountCreatedEndAt)),
            'add_expired_days' => $addExpiredDays,
        ];

        // mng_message_distributions登録データ生成
        $distributionCollection = collect();
        $displayOrder = 0;
        foreach ($this->itemSelected as $itemRow) {
            $distributionId = null;
            if (
                in_array(
                    $itemRow['distributionType'],
                    [
                        RewardType::UNIT->value,
                        RewardType::ITEM->value,
                        RewardType::EMBLEM->value,
                    ],
                    true
                )
            ) {
                // idが存在する種別ならidをセットする
                $distributionId = $itemRow['distributionId'];
            }

            $distributionOption = null;

            $distribution = [
                'display_order' => ++$displayOrder,
                'resource_type' => $itemRow['distributionType'],
                'resource_id' => $distributionId,
                'resource_amount' => $itemRow['distributionQuantity'],
            ];

            $distributionCollection->push($distribution);
        }

        // mng_messages_i18n登録データ生成
        $i18nCollection = collect();
        $i18nCollection->push(['language' => 'ja', 'title' => $this->titleJp, 'body' => $this->bodyJp]);

        $serializeMngMessage = serialize($mngMessageData);
        $serializeOprDistributions = serialize($distributionCollection);
        $serializeOprI18ns = serialize($i18nCollection);

        $targetType = AdmMessageTargetTypes::All->value;
        $targetIdsTxt = null;
        $targetIdInputType = AdmMessageTargetIdInputTypes::All->value;
        if (!$this->isAlDistribution) {
            // 個別配布の場合は各値をセット
            $targetType = $this->checkPlayerType;
            $targetIdsTxt = serialize($this->targetIdCollection->toArray());
            $targetIdInputType = $this->targetIdInputType;
        }

        return [
            $startAt,
            $expiredAt,
            $targetType,
            $targetIdsTxt,
            $targetIdInputType,
            $serializeMngMessage,
            $serializeOprDistributions,
            $serializeOprI18ns,
        ];
    }

    /**
     * ファイルアップロードした一時ファイルを削除する
     *
     * @return void
     */
    public function deleteTmpFiles(): void
    {
        foreach ($this->tmpFileNames as $fileName) {
            $disk = Storage::disk('local');
            if ($disk->exists("livewire-tmp/{$fileName}")) {
                $disk->delete("livewire-tmp/{$fileName}");
            }
        }
    }
}

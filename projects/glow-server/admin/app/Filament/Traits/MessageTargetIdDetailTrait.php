<?php

namespace App\Filament\Traits;

use App\Repositories\Adm\Base\AdmMessageDistributionInputRepositoryInterface;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Illuminate\Support\Collection;

trait MessageTargetIdDetailTrait
{
    public string $repository;

    private Collection $targetIdCollection;

    // 1タブ内で表示する対象IDの件数
    public const PER_PAGE_MAX = 10000;
    public string $targetType = '';
    public int $allTargetCount = 0;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * 画面初回遷移時に起動
     */
    public function traitMount(int | string $record): void
    {
        /** @var AdmMessageDistributionInputRepositoryInterface $admMessageDistributionInputRepository */
        $admMessageDistributionInputRepository = app()->make($this->repository);
        $admMessageDistributionInput = $admMessageDistributionInputRepository->getById($record);

        if (is_null($admMessageDistributionInput)) {
            // データがなければ一覧画面に遷移
            $this->redirect($this->getRedirectUrl());
        }

        $targetIdsTxt = $admMessageDistributionInput->getTargetIdsTxt();
        if (is_null($targetIdsTxt)) {
            // target_id_textがnullだったら一覧画面に遷移
            $this->redirect($this->getRedirectUrl());
        }

        // アンシリアライズして必要なデータをセット
        $targetIds = unserialize($targetIdsTxt, ['allowed_classes' => false]);
        $this->targetType = $admMessageDistributionInput->getTargetType();
        $this->targetIdCollection = collect($targetIds);
        $this->allTargetCount = $this->targetIdCollection->count();

        // 対象人数が多いと画面表示が重くなるので不要になった変数を削除
        unset($targetIds);
    }

    /**
     * メッセージ個別配布対象ID表示
     * TODO 可能ならプレイヤー名、プレイヤーレベルも表示できるようにしたい
     *
     * @param Infolist $infoList
     * @return Infolist
     */
    public function infoList(Infolist $infoList): Infolist
    {
        // 対象人数が大量だった場合を考慮してメモリとタイムアウト設定を拡張
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 300); // 5分

        // 1万件ずつのコレクションで分割し、1タブ内で表示する内容を生成
        $states = [];
        $tabs = [];
        foreach ($this->targetIdCollection->chunk(self::PER_PAGE_MAX) as $index => $chunkCollection) {
            $key = 'page' . $index + 1; // ページ数用に1加算
            $pageCount = $chunkCollection->count();

            $states[$key] = $chunkCollection;
            $tabs[] = Tabs\Tab::make($key)
                ->schema([
                    TextEntry::make($key)
                        ->label("対象ID({$pageCount}件)")
                        ->listWithLineBreaks(),
                ]);
        }
        $fields = [
            Tabs::make('Tabs')
                ->tabs($tabs)
        ];

        // 対象人数が多いと画面表示が重くなるので不要になった変数を削除
        unset($this->targetIdCollection);

        return $infoList
            ->state($states)
            ->schema($fields);
    }
}

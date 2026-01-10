<?php

namespace App\Filament\Pages\Mst;

use App\Domain\Resource\Mst\Models\MstModel;
use App\Filament\Pages\BnUserSearch\BnUserSearch;
use App\Traits\NotificationTrait;
use Filament\Pages\Dashboard;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Illuminate\Contracts\View\View;

/**
 * マスタデータ詳細画面の基底クラス
 *
 * ※ 一覧ページがResourceで作っている前提で作成しています。
 *   一覧ページがResourceではないパターンが出てきたら、その時拡張します。
 */
abstract class MstDetailBasePage extends Page
{
    use NotificationTrait;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'マスタ詳細';

    /**
     * パンくずリスト
     *
     * @var array<string, string>
     *   key: URL, value: ページ名
     */
    protected array $breadcrumbList = [];

    /**
     * ページ表示に必須なマスタデータモデル
     */
    protected ?MstModel $mstModel = null;

    public function mount()
    {
        $this->setBreadcrumbList();
    }

    public function getHeader(): ?View
    {
        return view(
            'filament.common.mst-detail-header',
            [
                'subTitle' => $this->getSubTitle(),
                'breadcrumbList' => $this->getBreadcrumbList(),
            ]
        );
    }

    /**
     * ページ表示に必須なマスタデータを取得する処理を実装する
     */
    abstract protected function getMstModelByQuery(): ?MstModel;

    /**
     * ページ表示に必須なマスタデータを取得する
     */
    protected function getMstModel(): ?MstModel
    {
        if ($this->mstModel !== null) {
            return $this->mstModel;
        }

        $this->mstModel = $this->getMstModelByQuery();
        if ($this->mstModel === null) {
            $this->sendMstNotFoundDangerNotification();
        }
        return $this->mstModel;
    }

    /**
     * ページ表示に必須なマスタデータが存在しない場合に、dangerで通知を表示してリダイレクトする
     *
     * 一覧ページのResourceがある場合は、その一覧ページにリダイレクトする
     * 一覧ページのResourceがない場合は、ホームにリダイレクトする
     *
     * @param string $notFoundDataLabel 存在しなかったデータのラベル
     * @param string $notFoundData 存在しなかったデータの値
     * @return void
     */
    protected function sendMstNotFoundDangerNotification(): void
    {
        $this->sendDangerNotification(
            title: 'マスタデータが見つかりませんでした',
            body: $this->getMstNotFoundDangerNotificationBody(),
        );

        $redirectUrl = $this->getRedicrectUrl();
        if (is_null($redirectUrl) === false) {
            $this->redirect($redirectUrl);
        } else {
            // リダイレクト先の指定がなければ、ホームへ戻る
            $this->redirect(BnUserSearch::getUrl());
        }
    }

    /**
     * リダイレクト先のURLを取得する
     * @return mixed
     */
    protected function getRedicrectUrl(): ?string
    {
        if ($this->hasResourceClass()) {
            return $this->getResourceClass()::getUrl();
        }

        return null;
    }

    /**
     * マスタデータが見つからなかった場合の通知の本文を取得する
     */
    protected function getMstNotFoundDangerNotificationBody(): string
    {
        return '';
    }

    /**
     * 一覧ページのResourceの指定があるかどうか
     * true: ある, false: ない
     * @return bool
     */
    protected function hasResourceClass(): bool
    {
        $resource = app($this->getResourceClass());
        if ($resource instanceof Resource) {
            return true;
        }

        return false;
    }

    /**
     * パンくずリストを設定する
     * @return void
     */
    private function setBreadcrumbList(): void
    {
        // ホーム
        $this->breadcrumbList = [
            BnUserSearch::getUrl() => 'ホーム',
        ];

        // 一覧ページ(Resourceがある場合のみ)
        $this->breadcrumbList = array_merge($this->breadcrumbList, $this->getListPageBreadcrumb());

        // 拡張時に追加
        $this->breadcrumbList = array_merge($this->breadcrumbList, $this->getAdditionalBreadcrumbs());

        // 自身のページ
        $urlParams = [];
        foreach ($this->queryString as $param) {
            if (isset($this->{$param}) === false) {
                continue;
            }
            $urlParams[$param] = $this->{$param};
        }
        $this->breadcrumbList = array_merge(
            $this->breadcrumbList,
            [
                self::getUrl($urlParams) => $this->getTitle(),
            ]
        );
    }

    /**
     * 一覧ページのパンくずリストを取得する
     * @return array<string, string>
     *  key: URL, value: ページ名
     */
    protected function getListPageBreadcrumb(): array
    {
        if ($this->hasResourceClass() === false) {
            return [];
        }

        return [
            $this->getResourceClass()::getUrl() => $this->getResourceClass()::getModelLabel(),
        ];
    }

    /**
     * パンくずリストに追加したい情報を取得する
     *
     * 子クラスで追加したい情報を指定して、オーバーライドすることで、ページに反映される
     *
     * @param array<string, string> $breadcrumbList
     *   key: URL, value: ページ名
     * @return array<mixed>
     */
    protected function getAdditionalBreadcrumbs(): array
    {
        return [];
    }

    protected function getBreadcrumbList(): array
    {
        return $this->breadcrumbList;
    }

    /**
     * 一覧ページがResourceクラスを使っているものであれば、そのクラス名を返すようにオーバーライドする
     *
     * ここで指定されたリソースクラスを一覧ページで使用している前提で処理を行なっています。
     */
    abstract protected function getResourceClass(): ?string;

    /**
     * サブタイトルを取得する
     */
    abstract protected function getSubTitle(): string;
}

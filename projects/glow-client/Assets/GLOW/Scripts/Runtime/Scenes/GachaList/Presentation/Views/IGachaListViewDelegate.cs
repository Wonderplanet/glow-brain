using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaContent.Presentation.Views;
using GLOW.Scenes.GachaList.Presentation.ViewModels;

namespace GLOW.Scenes.GachaList.Presentation.Views
{
    // interfaceを継承してるけど、あんまり良くないので後で考える
    public interface IGachaListViewDelegate : IGachaListContentViewDelegate
    {
        // ライフサイクル
        void OnViewDidLoad();
        void OnViewWillAppear();
        void OnViewDidUnLoad();

        // 初期化
        void UpdateView(MasterDataId initialShowOprGachaId);

        // ガシャ情報
        bool ShowGachaConfirmDialogView(MasterDataId oprGachaId, GachaDrawType gachaDrawType);
        void ShowGachaDetailDialogView(MasterDataId oprGachaId);
        void ShowGachaRatioDialogView(MasterDataId oprGachaId);
        void ShowGachaLineUpDialogView(MasterDataId oprGachaId);

        // 特商法とか
        void OnSpecificCommerceButtonTapped();
        void OnGachaHistoryButtonTapped();

        // チュートリアルガシャ
        void OnTutorialGachaDrawButtonTapped();

        // アセットバンドルロード
        void LoadGachaAsset(GachaContentAssetPath gachaContentAssetPath, Action onCompleted);
    }

    public interface IGachaListContentViewDelegate
    {
        void InitializeShowGachaContentView(MasterDataId oprGachaId);
        void ShowGachaContentView(MasterDataId oprGachaId);
        // 必殺ワザ
        void OnSpecialAttackButtonTapped(MasterDataId unitId);
        void OnUnitDetailViewButton(MasterDataId unitId);
    }
}

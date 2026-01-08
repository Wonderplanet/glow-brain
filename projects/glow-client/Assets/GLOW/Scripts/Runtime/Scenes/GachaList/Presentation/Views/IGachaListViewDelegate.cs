using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaContent.Presentation.Views;
using GLOW.Scenes.GachaList.Presentation.ViewModels;

namespace GLOW.Scenes.GachaList.Presentation.Views
{
    public interface IGachaListViewDelegate
    {
        void OnViewDidLoad();
        void OnViewDidUnLoad();
        void OnBannerTapped(MasterDataId gachaId);
        GachaListViewModel UpdateListView();
        bool ShowGachaConfirmDialogView(MasterDataId gachaId, GachaDrawType gachaDrawType);
        void ShowGachaDetailDialogView(MasterDataId gachaId);
        void ShowGachaRatioDialogView(MasterDataId gachaId);
        void ShowGachaLineUpDialogView(MasterDataId gachaId);
        GachaContentViewController CreateGachaContentViewController(MasterDataId gachaId);
        void OnTutorialGachaDrawButtonTapped();
        void OnGachaHistoryButtonTapped();
    }
}

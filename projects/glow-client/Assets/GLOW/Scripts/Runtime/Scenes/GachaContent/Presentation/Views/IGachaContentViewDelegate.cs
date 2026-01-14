using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaContent.Presentation.Views
{
    public interface IGachaContentViewDelegate
    {
        void UpdateView();
        void OnViewDidLoad();
        void OnViewDidUnLoad();
        void OnBackButtonTapped();
        void ShowGachaConfirmDialogView(MasterDataId gachaId, GachaDrawType gachaDrawType);
        void OnGachaDetailButtonTapped(MasterDataId gachaId);
        // void OnGachaProvisionRatioTapped(MasterDataId gachaId);
        void OnSpecificCommerceButtonTapped();
    }
}

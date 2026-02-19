using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PackShopGacha.Presentation.Views
{
    public interface IPackShopGachaViewDelegate
    {
        void OnViewDidLoad();
        void OnBannerTapped(MasterDataId gachaId);
        void OnClose();
    }
}
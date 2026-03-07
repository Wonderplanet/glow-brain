using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.PackShopGacha.Presentation.Views
{
    public interface IPackShopGachaViewDelegate
    {
        void OnViewDidLoad();
        void OnBannerTapped(MasterDataId gachaId, GachaType gachaType);
        void OnClose();
    }
}
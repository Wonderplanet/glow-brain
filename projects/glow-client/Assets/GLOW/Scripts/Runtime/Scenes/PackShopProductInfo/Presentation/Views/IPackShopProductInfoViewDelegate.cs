using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PackShopProductInfo.Presentation.Views
{
    public interface IPackShopProductInfoViewDelegate
    {
        void ViewDidLoad();
        void OnClose();
        void OnTicketDetailTapped(MasterDataId id);
    }
}

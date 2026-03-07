using GLOW.Core.Domain.ValueObjects;
using UIKit;

namespace GLOW.Scenes.PackShop.Presentation.Views
{
    public interface IPackShopViewController
    {
        UIViewController UIViewController { get; }
        void ShowProductInfo(MasterDataId mstPackId);
        void ShowPackShopGacha(MasterDataId ticketId, MasterDataId mstPackId, float targetPosY);
    }
}

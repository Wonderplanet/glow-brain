using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PackShop.Presentation.Views;
using GLOW.Scenes.PackShopProductInfo.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.PackShopProductInfo.Presentation.Views
{
    public class PackShopProductInfoViewController : UIViewController<PackShopProductInfoView>
    {
        public record Argument(MasterDataId OprProductId, IPackShopViewController PackShopViewController);

        [Inject] IPackShopProductInfoViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.ViewDidLoad();
        }

        public void Setup(PackShopProductInfoViewModel viewModel)
        {
            ActualView.Setup(viewModel, OnTicketDetailTapped);
        }
        
        void OnTicketDetailTapped(MasterDataId ticketId)
        {
            ViewDelegate.OnTicketDetailTapped(ticketId);
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnClose();
        }
    }
}

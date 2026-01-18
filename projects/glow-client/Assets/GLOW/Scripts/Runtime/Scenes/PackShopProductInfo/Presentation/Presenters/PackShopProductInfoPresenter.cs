using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.PackShopGacha.Presentation.Views;
using GLOW.Scenes.PackShopProductInfo.Domain.Models;
using GLOW.Scenes.PackShopProductInfo.Domain.UseCase;
using GLOW.Scenes.PackShopProductInfo.Presentation.ViewModels;
using GLOW.Scenes.PackShopProductInfo.Presentation.Views;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.PackShopProductInfo.Presentation.Presenters
{
    public class PackShopProductInfoPresenter : IPackShopProductInfoViewDelegate
    {
        [Inject] PackShopProductInfoViewController ViewController { get; }
        [Inject] PackShopProductInfoViewController.Argument Argument { get; }
        [Inject] GetPackProductInfoUseCase GetPackProductInfoUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }

        public void ViewDidLoad()
        {
            var model = GetPackProductInfoUseCase.GetProductInfo(Argument.OprProductId);
            var viewModel = Translate(model);
            ViewController.Setup(viewModel);
        }

        public void OnClose()
        {
            ViewController.Dismiss();
        }

        void IPackShopProductInfoViewDelegate.OnTicketDetailTapped(MasterDataId ticketId)
        {
            ViewController.Dismiss();

            var argument = new PackShopGachaViewController.Argument(ticketId, Argument.PackShopViewController, Argument.OprProductId);
            var controller = ViewFactory.Create<PackShopGachaViewController, PackShopGachaViewController.Argument>(argument);
            ViewController.PresentModally(controller);
        }

        PackShopProductInfoViewModel Translate(PackShopProductInfoModel model)
        {
            var contents = model.ContentModels
                .Select(TranslateContent)
                .ToList();
            var bonuses = model.BonusModels
                .Select(TranslateContent)
                .ToList();

            return new PackShopProductInfoViewModel(contents, bonuses);
        }

        PackShopProductInfoContentViewModel TranslateContent(PackShopProductInfoContentModel model)
        {
            var resourceIcon = PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(model.ResourceModel);
            return new PackShopProductInfoContentViewModel(
                resourceIcon,
                model.Name,
                model.Amount,
                model.IsTicketItemFlag);
        }
    }
}

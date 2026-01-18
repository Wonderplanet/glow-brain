using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ExchangeShop.Domain.UseCase;
using GLOW.Scenes.ExchangeShop.Presentation.Translator;
using GLOW.Scenes.ExchangeShop.Presentation.View;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ExchangeShop.Presentation.Presenter
{
    public class ExchangeShopTopPresenter : IExchangeShopTopViewDelegate
    {
        [Inject] ExchangeShopTopViewController ViewController { get; }
        [Inject] IHomeViewNavigation HomeNavigation { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] ExchangeShopTopViewController.Argument Argument { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] GetExchangeProductsUseCase GetExchangeProductsUseCase { get; }
        [Inject] CreateExchangeConfirmUseCase CreateExchangeConfirmUseCase { get; }

        UIIndexPath _updateCollectionIndexPath;

        void IExchangeShopTopViewDelegate.OnViewDidLoad()
        {
            ViewController.InitializeView();
            RefreshView();
        }

        void IExchangeShopTopViewDelegate.ShowTradeConfirmView(
            MasterDataId mstExchangeId,
            MasterDataId mstLineupId,
            PlayerResourceIconViewModel iconViewModel,
            UIIndexPath indexPath)
        {
            _updateCollectionIndexPath = indexPath;

            var useCaseModel = CreateExchangeConfirmUseCase.CreateExchangeConfirm(mstExchangeId, mstLineupId);
            var viewModel = ExchangeConfirmViewModelTranslator.Translate(useCaseModel);
            var argument = new ExchangeShopConfirmViewController.Argument(
                mstExchangeId,
                mstLineupId,
                viewModel,
                RefreshView,
                iconViewModel);
            var controller = ViewFactory.Create<ExchangeShopConfirmViewController,
                ExchangeShopConfirmViewController.Argument>(argument);
            ViewController.PresentModally(controller);
        }

        void IExchangeShopTopViewDelegate.OnItemIconButtonTapped(PlayerResourceIconViewModel iconViewModel)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(iconViewModel, ViewController);
        }

        void IExchangeShopTopViewDelegate.OnBackButtonTapped()
        {
            HomeNavigation.TryPop();
        }

        void RefreshView()
        {
            // LineupIdからUseCaseを介して、ViewModelを作成して渡す
            var useCaseModel = GetExchangeProductsUseCase.GetTradeShopProducts(Argument.MstExchangeId);
            var viewModel = ExchangeShopTopViewModelTranslator.Translate(useCaseModel);
            ViewController.SetUpView(viewModel);

            // 画面更新は、CollectionViewのReloadDataで更新すると
            // セルのSetupが正しく行われないため、交換した商品のみで更新を行う
            // (原因は不明、ブレークポイントを仕掛けるとコード的には正常であったため、恐らくCollectionView側の問題と思われる)
            ViewController.UpdateCollectionViewCell(_updateCollectionIndexPath);
        }
    }
}

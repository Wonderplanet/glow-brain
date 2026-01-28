using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Modules.CommonReceiveView.Domain.UseCases;
using GLOW.Modules.CommonWebView.Presentation.Control;
using GLOW.Scenes.GachaConfirm.Presentation.Views;
using GLOW.Scenes.GachaContent.Domain.UseCases;
using GLOW.Scenes.GachaContent.Presentation.Views;
using GLOW.Scenes.GachaDetailDialog.Domain.UseCases;
using GLOW.Scenes.GachaList.Presentation.Translator;
using GLOW.Scenes.GachaRatio.Domain.Model;
using GLOW.Scenes.GachaRatio.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.GachaContent.Presentation.Presenters
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-1_ガシャトップ
    /// </summary>
    public class GachaContentPresenter: IGachaContentViewDelegate
    {
        [Inject] GachaContentViewController GachaContentViewController { get; }
        [Inject] GachaContentViewController.Argument Argument { get; }
        [Inject] IGachaListElementUseCaseModelFactory GachaListElementUseCaseModelFactory { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] ICommonWebViewControl CommonWebViewControl { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] GachaWireFrame.Presentation.Presenters.GachaWireFrame GachaWireFrame { get; }
        [Inject] GachaRatioDialogUseCase GachaRatioDialogUseCase { get; }
        [Inject] GachaDetailDialogUseCase GachaDetailDialogUseCase { get; }
        [Inject] GetCommonReceiveItemUseCase GetCommonReceiveItemUseCase { get; }

        void IGachaContentViewDelegate.OnViewDidLoad()
        {
            // GachaWireFrame.RegisterGachaContentViewController(GachaContentViewController);

            // var gachaContentViewModel = GetViewModel();
            // GachaContentViewController.SetViewModel(gachaContentViewModel);
        }

        void IGachaContentViewDelegate.OnViewDidUnLoad()
        {
            // GachaWireFrame.UnregisterGachaContentViewController();
        }

        void IGachaContentViewDelegate.UpdateView()
        {
            // var gachaContentViewModel = GetViewModel();
            // GachaContentViewController.UpdateContentView(gachaContentViewModel);
        }

        void IGachaContentViewDelegate.OnBackButtonTapped()
        {
           HomeViewNavigation.TryPop();
        }

        void IGachaContentViewDelegate.ShowGachaConfirmDialogView(MasterDataId gachaId, GachaDrawType gachaDrawType)
        {
            var argument = new GachaConfirmDialogViewController.Argument(gachaId,gachaDrawType);
            var viewController = ViewFactory.Create<GachaConfirmDialogViewController, GachaConfirmDialogViewController.Argument>(argument);
            GachaContentViewController.PresentModally(viewController);
        }

        void IGachaContentViewDelegate.OnGachaDetailButtonTapped(MasterDataId gachaId)
        {
            // ガシャ詳細
            ShowGachaDetailDialogView(gachaId);
        }

        void IGachaContentViewDelegate.OnSpecificCommerceButtonTapped()
        {
            CommonWebViewControl.ShowWebView(WebViewShownContentType.SpecificCommerce);
        }

        void ShowGachaDetailDialogView(MasterDataId gachaId)
        {
            DoAsync.Invoke(GachaContentViewController.ActualView, ScreenInteractionControl, async ct =>
            {
                var useCaseModel = await GachaDetailDialogUseCase.GetGachaDetailUseCaseModel(ct, gachaId);
                var viewModel = GachaDetailDialogViewModelTranslator.TranslateToViewModel(useCaseModel);

                GachaWireFrame.ShowGachaDetailView(gachaId, viewModel, GachaContentViewController);
            });
        }

        void OnClickIconDetail(GachaRatioResourceModel resourceModel)
        {
            var playerResourceModel = GetCommonReceiveItemUseCase.GetPlayerResource(
                resourceModel.ResourceType,
                resourceModel.MasterDataId,
                resourceModel.Amount
            );

            GachaWireFrame.OnClickIconDetail(playerResourceModel, GachaContentViewController);
        }
    }
}

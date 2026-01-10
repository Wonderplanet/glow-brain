using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Modules.CommonReceiveView.Domain.UseCases;
using GLOW.Modules.CommonWebView.Presentation.Control;
using GLOW.Scenes.EncyclopediaUnitSpecialAttack.Presentation.Views;
using GLOW.Scenes.GachaConfirm.Presentation.Views;
using GLOW.Scenes.GachaContent.Domain.UseCases;
using GLOW.Scenes.GachaContent.Presentation.Translator;
using GLOW.Scenes.GachaContent.Presentation.ViewModels;
using GLOW.Scenes.GachaContent.Presentation.Views;
using GLOW.Scenes.GachaDetailDialog.Domain.UseCases;
using GLOW.Scenes.GachaList.Presentation.Translator;
using GLOW.Scenes.GachaRatio.Domain.Model;
using GLOW.Scenes.GachaRatio.Domain.UseCases;
using GLOW.Scenes.GachaRatio.Presentation.Translator;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.UnitDetailModal.Presentation.Views;
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
        [Inject] HomeViewController HomeViewController { get; }
        [Inject] GachaContentViewController.Argument Argument { get; }
        [Inject] GachaContentUseCase GachaContentUseCase { get; }
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
            GachaWireFrame.RegisterGachaContentViewController(GachaContentViewController);

            var gachaContentViewModel = GetViewModel();
            GachaContentViewController.SetViewModel(gachaContentViewModel);
        }

        void IGachaContentViewDelegate.OnViewDidUnLoad()
        {
            GachaWireFrame.UnregisterGachaContentViewController();
        }

        void IGachaContentViewDelegate.UpdateView()
        {
            var gachaContentViewModel = GetViewModel();
            GachaContentViewController.UpdateViewModel(gachaContentViewModel);
        }

        GachaContentViewModel GetViewModel()
        {
            var useCaseModel = GachaContentUseCase.GetGachaContentUseCaseModel(Argument.GachaId);
            return GachaContentTranslator.Translate(useCaseModel);
        }

        void IGachaContentViewDelegate.OnBackButtonTapped()
        {
           HomeViewNavigation.TryPop();
        }

        void IGachaContentViewDelegate.ShowGachaConfirmDialogView(MasterDataId gachaId, GachaDrawType gachaDrawType, GachaDrawFromContentViewFlag gachaDrawFromContentViewFlag)
        {
            var argument = new GachaConfirmDialogViewController.Argument(gachaId,gachaDrawType);
            var viewController = ViewFactory.Create<GachaConfirmDialogViewController, GachaConfirmDialogViewController.Argument>(argument);
            viewController.IsGachaDrawFromContentView = gachaDrawFromContentViewFlag;
            GachaContentViewController.PresentModally(viewController);
        }

        void IGachaContentViewDelegate.OnUnitDetailViewButton(MasterDataId unitId)
        {
            var argument = new UnitDetailModalViewController.Argument(unitId, MaxStatusFlag.True);
            var controller = ViewFactory.Create<UnitDetailModalViewController, UnitDetailModalViewController.Argument>(argument);
            GachaContentViewController.PresentModally(controller);
        }

        void IGachaContentViewDelegate.OnGachaProvisionRatioTapped(MasterDataId gachaId)
        {
            // ガシャ提供割合
            ShowGachaRatioDialogView(gachaId);
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

        void IGachaContentViewDelegate.OnSpecialAttackButtonTapped(MasterDataId unitId)
        {
            var argument = new UnitSpecialAttackPreviewViewController.Argument(unitId);
            var viewController = ViewFactory.Create<UnitSpecialAttackPreviewViewController, UnitSpecialAttackPreviewViewController.Argument>(argument);

            GachaContentViewController.DisableScroll();
            viewController.OnClose = () =>
            {
                GachaContentViewController.EnableScroll();
            };

            // ヘッダーよりも上に表示するため、HomeViewControllerを使って表示する
            HomeViewController.Show(viewController);
        }

        void ShowGachaRatioDialogView(MasterDataId gachaId)
        {
            // ガシャ提供割合ダイアログを開く
            DoAsync.Invoke(GachaContentViewController.ActualView, ScreenInteractionControl, async ct =>
            {
                var useCaseModel = await GachaRatioDialogUseCase.GetGachaRatioUseCaseModel(ct, gachaId);
                var viewModel = GachaRatioDialogViewModelTranslator.TranslateToViewModel(useCaseModel, OnClickIconDetail);
                
                GachaWireFrame.ShowGachaRatioDialogView(gachaId, viewModel, GachaContentViewController);
            });
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

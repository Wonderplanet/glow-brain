using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.CommonWebView.Presentation.Control;
using GLOW.Scenes.BattleResult.Presentation.Views;
using GLOW.Scenes.GachaConfirm.Domain.Model;
using GLOW.Scenes.GachaConfirm.Domain.UseCases;
using GLOW.Scenes.GachaConfirm.Presentation.ViewModels;
using GLOW.Scenes.GachaConfirm.Presentation.Views;
using GLOW.Scenes.GachaList.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.PassShop.Presentation.Translator;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.GachaConfirm.Presentation.Presenters
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-19_ガシャ確認ダイアログ
    /// </summary>
    public class GachaConfirmDialogPresenter : IGachaConfirmDialogViewDelegate
    {
        [Inject] GachaConfirmDialogUseCase UseCase { get; }
        [Inject] GachaConfirmDialogViewController.Argument Argument { get; }
        [Inject] GachaConfirmDialogViewController ViewController { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] ICommonWebViewControl CommonWebViewControl { get; }
        [Inject] IGachaDrawControl GachaDrawControl { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] CheckContentMaintenanceUseCase CheckContentMaintenanceUseCase { get; }
        [Inject] ContentMaintenanceWireframe ContentMaintenanceWireframe { get; }

        public void OnViewDidLoad()
        {
            UpdateView();
        }

        void IGachaConfirmDialogViewDelegate.GachaDraw(
            MasterDataId gachaId,
            GachaType gachaType,
            GachaDrawCount drawCount,
            CostType costType,
            CostAmount costAmount,
            MasterDataId costId,
            bool isReDraw,
            GachaDrawFromContentViewFlag isGachaDrawFromContentView)
        {
            GachaDrawControl.GachaDraw(
                gachaId, 
                gachaType, 
                drawCount, 
                costType, 
                Argument.GachaDrawType,
                costAmount, 
                costId, 
                isReDraw, 
                isGachaDrawFromContentView);
            ViewController.CloseDialog();
        }

        void IGachaConfirmDialogViewDelegate.TutorialGachaDraw()
        {
            // GachaContentから引くのでfalseで呼び出し
            GachaDrawControl.TutorialGachaDraw(false);
            ViewController.CloseDialog();
        }

        void IGachaConfirmDialogViewDelegate.OnSpecificCommerceButtonTapped()
        {
            CommonWebViewControl.ShowWebView(WebViewShownContentType.SpecificCommerce);
        }

        void IGachaConfirmDialogViewDelegate.TransitionToShopView()
        {
            // 購入ダイアログを表示する
            ShowPurchaseDialog();
        }

        void UpdateView()
        {
            var useCaseModel = UseCase.GetUseCaseModel(Argument.GachaId, Argument.GachaDrawType);
            var viewModel = Translator(useCaseModel);
            ViewController.SetViewModel(viewModel);
        }

        GachaConfirmDialogViewModel Translator(GachaConfirmDialogUseCaseModel useCaseModel)
        {
            return new GachaConfirmDialogViewModel(
                GachaId: useCaseModel.GachaId,
                GachaType: useCaseModel.GachaType,
                CostId: useCaseModel.CostId,
                GachaDrawType: Argument.GachaDrawType,
                CostType: useCaseModel.CostType,
                DrawableFlag: useCaseModel.DrawableFlag,
                GachaName: useCaseModel.GachaName,
                CostAmount: useCaseModel.CostAmount,
                CostName: useCaseModel.CostName,
                GachaDrawCount: useCaseModel.GachaDrawCount,
                PlayerResourceIconAssetPath: useCaseModel.PlayerResourceIconAssetPath,
                PlayerItemAmount: useCaseModel.PlayerItemAmount,
                PlayerFreeDiamondAmount: useCaseModel.PlayerFreeDiamondAmount,
                PlayerFreeDiamondAmountAfterConsumption: useCaseModel.PlayerFreeDiamondAmountAfterConsumption,
                PlayerPaidDiamondAmount: useCaseModel.PlayerPaidDiamondAmount,
                PlayerPaidDiamondAmountAfterConsumption: useCaseModel.PlayerPaidDiamondAmountAfterConsumption,
                AdGachaResetRemainingTimeSpan: useCaseModel.AdGachaResetRemainingTimeSpan,
                AdGachaDrawableCount: useCaseModel.AdGachaDrawableCount,
                HeldAdSkipPassInfoViewModel: HeldAdSkipPassInfoViewModelTranslator.ToHeldAdSkipPassInfoViewModel(
                    useCaseModel.HeldAdSkipPassInfoModel)
            );
        }

        void ShowPurchaseDialog()
        {
            if (CheckContentMaintenanceUseCase.IsInMaintenance(ContentMaintenanceTarget.ShopItem))
            {
                ContentMaintenanceWireframe.ShowDialog();
                return;
            }

            // ヘッダー更新をコールバックで行う
            DiamondPurchaseViewController.Argument argument
                = new DiamondPurchaseViewController.Argument(UpdateDisplayPlayerResource);

            var viewController =
                ViewFactory.Create<DiamondPurchaseViewController, DiamondPurchaseViewController.Argument>(argument);

            ViewController.PresentModally(viewController);
        }

        void UpdateDisplayPlayerResource()
        {
            // プレイヤーリソース表示更新
            HomeHeaderDelegate.UpdateStatus();

            // ダイアログの表示更新
            UpdateView();
        }
    }
}

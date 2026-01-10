using System;
using Cysharp.Text;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.CommonWebView.Presentation.Control;
using GLOW.Scenes.BattleResult.Presentation.Views;
using GLOW.Scenes.DiamondConsumeConfirm.Domain.Enumerable;
using GLOW.Scenes.DiamondConsumeConfirm.Domain.ValueObjects;
using GLOW.Scenes.DiamondConsumeConfirm.Presentation.ViewModels;
using GLOW.Scenes.DiamondConsumeConfirm.Presentation.Views;
using GLOW.Scenes.ShopBuyConform.Domain.Model;
using GLOW.Scenes.ShopBuyConform.Domain.UseCase;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.DiamondConsumeConfirm.Presentation.Presenters
{
    /// <summary>
    /// 131_共通
    /// 　131-3_購入確認ダイアログ
    /// 　　131-5-1_購入確認ダイアログ（アプリ専用通貨 主に一次通貨）
    /// 　　131-3-4_アプリ専用通貨不足時ダイアログ
    ///
    /// DiamondBuyConfirmのバリアント
    /// 汎用的な一次通貨消費確認ダイアログ
    /// </summary>
    public class DiamondConsumeConfirmPresenter : IDiamondConsumeConfirmViewDelegate
    {
        [Inject] DiamondConsumeConfirmViewController ViewController { get; }

        [Inject] DiamondConsumeConfirmViewController.Argument Argument { get; }
        [Inject] CurrentPlayerResourceInfoUseCase CurrentPlayerResourceInfoUseCase { get; }
        [Inject] ICommonWebViewControl CommonWebViewControl { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] CheckContentMaintenanceUseCase CheckContentMaintenanceUseCase { get; }
        [Inject] ContentMaintenanceWireframe ContentMaintenanceWireframe { get; }
        bool _isEnough;

        void IDiamondConsumeConfirmViewDelegate.ViewDidLoad()
        {
            UpdateView();
        }

        void IDiamondConsumeConfirmViewDelegate.SpecificCommerceButtonTapped()
        {
            CommonWebViewControl.ShowWebView(WebViewShownContentType.SpecificCommerce);
        }

        void IDiamondConsumeConfirmViewDelegate.ConfirmButtonTapped()
        {
            if (!_isEnough)
            {
                ShowBuyDiamondView();
                return;
            }

            Argument.OnConfirm?.Invoke();
            ViewController.Dismiss();
        }

        void IDiamondConsumeConfirmViewDelegate.CancelButtonTapped()
        {
            ViewController.Dismiss();
            Argument.OnCancel?.Invoke();
        }

        void UpdateView()
        {
            var model = CurrentPlayerResourceInfoUseCase.GetCurrentPlayerResourceAmount();
            _isEnough = Argument.ConsumeDiamond <= (model.CurrentPaidDiamond + model.CurrentFreeDiamond);

            var viewModel = TranslateViewModel(model, _isEnough);
            ViewController.Setup(viewModel);
        }

        DiamondConsumeConfirmViewModel TranslateViewModel(CurrentPlayerResourceInfoUseCaseModel model, bool isEnough)
        {
            var calculatedDiamond = DiamondCalculator.CalculateAfterDiamonds(model.CurrentPaidDiamond,
                model.CurrentFreeDiamond, Argument.ConsumeDiamond);
            var text = GetText(Argument.Type, Argument.ConsumeDiamond, isEnough);

            return new DiamondConsumeConfirmViewModel(
                text,
                model.CurrentPaidDiamond,
                calculatedDiamond.paid,
                model.CurrentFreeDiamond,
                calculatedDiamond.free,
                new EnoughCostFlag(isEnough)
                );
        }

        DiamondConsumeConfirmText GetText(ConsumeType type, TotalDiamond consumeDiamond, bool isEnough)
        {
            return type switch
            {
                ConsumeType.QuickIdleIncentive => new DiamondConsumeConfirmText(
                    "確認",
                    ZString.Format("プリズムを{0}個使用してクイック探索を実行しますか？", consumeDiamond.Value),
                    isEnough ? "使用する" : "購入"),
                _ => throw new Exception($"Not implemented ConsumeType...{type}")
            };
        }

        void ShowBuyDiamondView()
        {
            if (CheckContentMaintenanceUseCase.IsInMaintenance(ContentMaintenanceTarget.ShopItem ))
            {
                ContentMaintenanceWireframe.ShowDialog();
                return;
            }

            var argument = new DiamondPurchaseViewController.Argument(UpdateView);

            var viewController =
                ViewFactory.Create<DiamondPurchaseViewController, DiamondPurchaseViewController.Argument>(argument);
            ViewController.PresentModally(viewController);
        }
    }
}

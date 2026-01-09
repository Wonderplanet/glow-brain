using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Exceptions;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.CommonWebView.Presentation.Control;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.BattleResult.Domain.UseCases;
using GLOW.Scenes.BattleResult.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Modules.Log;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.BattleResult.Presentation.Presenters
{
    /// <summary>
    /// 53_バトルリザルト
    /// 　53-2_敗北リザルト
    /// 　　53-2-1-2_コンティニュー
    /// 　　　53-2-1-2-2_コンティニューダイアログ（プリズム）
    /// </summary>
    public class ContinueDiamondPresenter : IContinueDiamondViewDelegate
    {
        [Inject] ContinueDiamondViewController.Argument Argument { get; }
        [Inject] ContinueDiamondViewController ViewController { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] ICommonWebViewControl CommonWebViewControl { get; }
        [Inject] ContinueDiamondUseCase ContinueDiamondUseCase { get; }
        [Inject] IPeriodOutsideExceptionWireframe PeriodOutsideExceptionWireframe { get; }

        ContinueDiamondViewController.Result _result = ContinueDiamondViewController.Result.Cancel;

        void IContinueDiamondViewDelegate.OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(ContinueDiamondPresenter), nameof(IContinueDiamondViewDelegate.OnViewDidLoad));

            ViewController.SetUp(Argument.ViewModel);
        }

        void IContinueDiamondViewDelegate.OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(ContinueDiamondPresenter), nameof(IContinueDiamondViewDelegate.OnViewDidUnload));

            Argument.OnViewClosed?.Invoke(_result);
        }

        void IContinueDiamondViewDelegate.OnCancelSelected()
        {
            ApplicationLog.Log(nameof(ContinueDiamondPresenter), nameof(IContinueDiamondViewDelegate.OnCancelSelected));

            _result = ContinueDiamondViewController.Result.Cancel;
            ViewController.Dismiss();
        }

        void IContinueDiamondViewDelegate.OnSpecificCommerceSelected()
        {
            ApplicationLog.Log(nameof(ContinueDiamondPresenter), nameof(IContinueDiamondViewDelegate.OnSpecificCommerceSelected));

            // 汎用ダイアログ 表示確認のため
            CommonWebViewControl.ShowWebView(WebViewShownContentType.SpecificCommerce);
        }

        void IContinueDiamondViewDelegate.OnContinueDiamondSelected()
        {
            ApplicationLog.Log(nameof(ContinueDiamondPresenter), nameof(IContinueDiamondViewDelegate.OnContinueDiamondSelected));

            DoAsync.Invoke(ViewController.ActualView, ScreenInteractionControl, async cancellationToken =>
            {
                try
                {
                    await ContinueDiamondUseCase.Continue(cancellationToken);

                    _result = ContinueDiamondViewController.Result.Continue;
                    ViewController.Dismiss();
                }
                catch (Exception ex) when (
                    ex is QuestPeriodOutsideException
                        or EventPeriodOutsideException)
                {
                    // クエスト・イベント開催期間外であればダイアログ表示の上、キャンセルに以降
                    PeriodOutsideExceptionWireframe.ShowPeriodOutsideExceptionMessage(
                        ex,
                        () =>
                        {
                            _result = ContinueDiamondViewController.Result.QuestPeriodOutside;
                            ViewController.Dismiss();
                        });
                }
            });
        }

        void IContinueDiamondViewDelegate.OnPurchaseSelected()
        {
            ApplicationLog.Log(nameof(ContinueDiamondPresenter), nameof(IContinueDiamondViewDelegate.OnPurchaseSelected));

            _result = ContinueDiamondViewController.Result.Purchase;
            ViewController.Dismiss();
        }
    }
}

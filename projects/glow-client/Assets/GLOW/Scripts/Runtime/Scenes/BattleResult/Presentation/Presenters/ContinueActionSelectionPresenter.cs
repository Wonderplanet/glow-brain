using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Exceptions;
using GLOW.Core.Modules.Advertising.AdfurikunAgent;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.BattleResult.Domain.UseCases;
using GLOW.Scenes.BattleResult.Presentation.Translators;
using GLOW.Scenes.BattleResult.Presentation.Views;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Presentation.Views;
using GLOW.Scenes.PassShop.Domain.UseCase;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Modules.Log;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.BattleResult.Presentation.Presenters
{
    /// <summary>
    /// 53_バトルリザルト
    /// 　53-2_敗北リザルト
    /// 　　53-2-1-2_コンティニュー
    /// 　　　53-2-1-2-1_コンティニュー確認ダイアログ
    /// </summary>
    public class ContinueActionSelectionPresenter : IContinueActionSelectionViewDelegate
    {
        [Inject] ContinueActionSelectionViewController.Argument Argument { get; }
        [Inject] ContinueActionSelectionViewController SelectionViewController { get; }
        [Inject] InGameViewController InGameViewController { get; }
        [Inject] InAppAdvertisingWireframe InAppAdvertisingWireframe { get; }
        [Inject] IPeriodOutsideExceptionWireframe PeriodOutsideExceptionWireframe { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] ContinueAdUseCase ContinueAdUseCase { get; }
        [Inject] GetContinueDiamondUseCase GetContinueDiamondUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] GetHeldAdSkipPassInfoUseCase GetHeldAdSkipPassInfoUseCase { get; }
        [Inject] CheckContentMaintenanceUseCase CheckContentMaintenanceUseCase { get; }
        [Inject] ContentMaintenanceWireframe ContentMaintenanceWireframe { get; }

        ContinueActionSelectionViewController.Result _result = ContinueActionSelectionViewController.Result.Cancel;

        void IContinueActionSelectionViewDelegate.OnViewDidLoad()
        {
            ApplicationLog.Log(
                nameof(ContinueActionSelectionPresenter),
                nameof(IContinueActionSelectionViewDelegate.OnViewDidLoad));

            SelectionViewController.SetUp(Argument.ViewModel);
        }

        void IContinueActionSelectionViewDelegate.OnViewDidUnload()
        {
            ApplicationLog.Log(
                nameof(ContinueActionSelectionPresenter),
                nameof(IContinueActionSelectionViewDelegate.OnViewDidUnload));

            Argument.OnViewClosed?.Invoke(_result);
        }

        void IContinueActionSelectionViewDelegate.OnCancelSelected()
        {
            ApplicationLog.Log(
                nameof(ContinueActionSelectionPresenter),
                nameof(IContinueActionSelectionViewDelegate.OnCancelSelected));

            _result = ContinueActionSelectionViewController.Result.Cancel;
            SelectionViewController.Dismiss();
        }

        void IContinueActionSelectionViewDelegate.OnContinueDiamondSelected()
        {
            ApplicationLog.Log(
                nameof(ContinueActionSelectionPresenter),
                nameof(IContinueActionSelectionViewDelegate.OnContinueDiamondSelected));

            HideView();
            DoAsync.Invoke(SelectionViewController.ActualView, async cancellationToken =>
            {
                while (true)
                {
                    var continueModel = GetContinueDiamondUseCase.GetModel();
                    ContinueConfirmationResult continueConfirmationResult = await ShowContinueDiamondView(
                        continueModel,
                        cancellationToken);

                    switch (continueConfirmationResult)
                    {
                        // プリズム購入画面を出し、その後もう一度プリズムでのコンティニュー画面を出す
                        // この画面と違いResumeViewなどで操作しないのは購入による反映させるのに情報の更新が必要なため
                        case ContinueConfirmationResult.Purchase:
                            await ShowDiamondPurchaseView(cancellationToken);
                            continue;

                        // キャンセル時はそのままコンティニュー方法選択画面に戻る
                        case ContinueConfirmationResult.Cancel:
                            ResumeView();
                            return;

                        // プリズムでのコンティニュー完了
                        case ContinueConfirmationResult.Continue:
                            ResumeView();
                            _result = ContinueActionSelectionViewController.Result.Continue;
                            SelectionViewController.Dismiss(false);
                            return;

                        // クエスト期間外の場合はコンティニュー不可のため表示を閉じてGAME OVER画面に移行
                        case ContinueConfirmationResult.QuestPeriodOutside:
                            ResumeView();
                            _result = ContinueActionSelectionViewController.Result.QuestPeriodOutside;
                            SelectionViewController.Dismiss(false);
                            return;
                    }
                }
            });
        }

        void IContinueActionSelectionViewDelegate.OnContinueAdSelected()
        {
            ApplicationLog.Log(
                nameof(ContinueActionSelectionPresenter),
                nameof(IContinueActionSelectionViewDelegate.OnContinueAdSelected));

            DoAsync.Invoke(SelectionViewController.ActualView, ScreenInteractionControl, async cancellationToken =>
            {
                try
                {
                    // 視聴完了後に報酬を反映させるため、広告表示後にAPIを実行する形
                    if (GetHeldAdSkipPassInfoUseCase.GetHeldAdSkipPassInfo().IsEmpty())
                    {
                        // 広告スキップパスを持っていない場合は広告を表示
                        var adResultType =
                            await InAppAdvertisingWireframe.ShowAdAsync(
                                IAARewardFeatureType.Continue,
                                cancellationToken);
                        if (adResultType == AdResultType.Cancelled)
                        {
                            // cancelのときは何もしない
                            return;
                        }
                    }

                    await ContinueAdUseCase.Continue(cancellationToken);

                    _result = ContinueActionSelectionViewController.Result.Continue;
                    SelectionViewController.Dismiss();
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
                            _result = ContinueActionSelectionViewController.Result.QuestPeriodOutside;
                            SelectionViewController.Dismiss();
                        });
                }
            });
        }

        /// <summary> プリズムでのコンティニュー画面表示 /// </summary>
        async UniTask<ContinueConfirmationResult> ShowContinueDiamondView(
            ContinueDiamondModel continueDiamondModel,
            CancellationToken cancellationToken)
        {
            var continueViewModel = ContinueDiamondViewModelTranslator.ToContinueViewModel(continueDiamondModel);

            bool isClosed = false;
            ContinueDiamondViewController.Result continueViewResult = ContinueDiamondViewController.Result.Cancel;

            var argument = new ContinueDiamondViewController.Argument(
                continueViewModel,
                result =>
                {
                    continueViewResult = result;
                    isClosed = true;
                });

            var diamondViewController =
                ViewFactory.Create<ContinueDiamondViewController, ContinueDiamondViewController.Argument>(argument);

            diamondViewController.OnCancel = ResumeView;

            InGameViewController.PresentModally(diamondViewController);

            await UniTask.WaitUntil(() => isClosed, cancellationToken: cancellationToken);

            return continueViewResult switch
            {
                ContinueDiamondViewController.Result.Cancel => ContinueConfirmationResult.Cancel,
                ContinueDiamondViewController.Result.Continue => ContinueConfirmationResult.Continue,
                ContinueDiamondViewController.Result.Purchase => ContinueConfirmationResult.Purchase,
                ContinueDiamondViewController.Result.QuestPeriodOutside => ContinueConfirmationResult.QuestPeriodOutside,
                _ => ContinueConfirmationResult.Cancel
            };
        }

        /// <summary> プリズム購入画面表示 /// </summary>
        async UniTask ShowDiamondPurchaseView(CancellationToken cancellationToken)
        {
            var isClosed = false;
            if (CheckContentMaintenanceUseCase.IsInMaintenance(ContentMaintenanceTarget.ShopItem))
            {
                ContentMaintenanceWireframe.ShowDialog(() => isClosed = true);
                await UniTask.WaitUntil(() => isClosed, cancellationToken: cancellationToken);
                return;
            }

            var argument = new DiamondPurchaseViewController.Argument(() => isClosed = true);

            var viewController =
                ViewFactory.Create<DiamondPurchaseViewController, DiamondPurchaseViewController.Argument>(argument);

            InGameViewController.PresentModally(viewController);

            await UniTask.WaitUntil(() => isClosed, cancellationToken: cancellationToken);
        }

        void HideView()
        {
            SelectionViewController.Parent.View.Hidden = true;
        }

        void ResumeView()
        {
            SelectionViewController.Parent.View.Hidden = false;
        }
    }
}

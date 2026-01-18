using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Modules.Advertising.AdfurikunAgent;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.Views.InAppAdvertisingConfirmView;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Modules.TutorialTipDialog.Domain.Definitions;
using GLOW.Modules.TutorialTipDialog.Presentation.View;
using GLOW.Scenes.EventBonusUnitList.Presentation.Views;
using GLOW.Scenes.EnhanceQuestScoreDetail.Presentation.Views;
using GLOW.Scenes.EnhanceQuestTop.Domain.Models;
using GLOW.Scenes.EnhanceQuestTop.Domain.UseCases;
using GLOW.Scenes.EnhanceQuestTop.Presentation.Translators;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Presenters;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.HomePartyFormation.Presentation.Views;
using GLOW.Scenes.PassShop.Domain.UseCase;
using GLOW.Scenes.PassShop.Presentation.Translator;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Application.Modules;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.EnhanceQuestTop.Presentation.Views
{
    /// <summary>
    /// 45_強化クエスト
    /// 　42-5_1日N回強化クエスト
    /// 　　42-5-2_ 1日N回コイン獲得クエストTOP画面
    /// </summary>
    public class EnhanceQuestTopPresenter : IEnhanceQuestTopViewDelegate
    {
        [Inject] EnhanceQuestTopViewController ViewController { get; }
        [Inject] EnhanceQuestTopUseCase UseCase { get; }
        [Inject] SetPartyFormationEventBonusUseCase SetPartyFormationEventBonusUseCase { get; }
        [Inject] GetCurrentPartyNameUseCase GetCurrentPartyNameUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] StartStageWireFrame StartStageWireFrame { get; }
        [Inject] IHomeViewDelegate HomeViewDelegate { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] TutorialTipDialogViewWireFrame TutorialTipDialogViewWireFrame { get; }
        [Inject] ILocalNotificationScheduler LocalNotificationScheduler { get; }
        [Inject] CheckFreePartTutorialCompletedUseCase CheckFreePartTutorialCompletedUseCase { get; }
        [Inject] CompleteFreePartTutorialUseCase CompleteFreePartTutorialUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] InAppAdvertisingWireframe InAppAdvertisingWireframe { get; }
        [Inject] GetHeldAdSkipPassInfoUseCase GetHeldAdSkipPassInfoUseCase { get; }
        [Inject] UpdateEnhanceQuestTopUseCase UpdateEnhanceQuestTopUseCase { get; }

        EnhanceQuestTopModel _model;

        void IEnhanceQuestTopViewDelegate.OnViewDidLoad()
        {
            _model = UseCase.GetEnhanceQuestTop();
            var viewModel = Translate(_model);
            ViewController.Setup(viewModel);
        }

        void IEnhanceQuestTopViewDelegate.OnViewWillAppear()
        {
            var model = UpdateEnhanceQuestTopUseCase.UpdateEnhanceQuestTop();
            var viewModel = UpdatedEnhanceQuestTopViewModelTranslator.ToViewModel(model);
            ViewController.UpdateTopView(viewModel);

            // 初回遷移時にダイアログ表示をする
            ShowTutorialDialogIfNeed();
        }

        void IEnhanceQuestTopViewDelegate.OnEnhanceQuestButtonTapped()
        {
            if (!_model.ChallengeCount.IsEnough())
            {
                ShowLimitView();
                return;
            }

            ViewController.View.UserInteraction = false;
            HomeViewDelegate.ShowTapBlock(false, null, 0f);
            // インゲームへ遷移
            StartStageWireFrame.StartStage(ViewController.View, _model.MstStageId, StaminaBoostCount.One, didStart =>
            {
                if (didStart)
                {
                    LocalNotificationScheduler.RefreshRemainCoinQuestSchedule();
                    return;
                }

                // NOTE: 開始できない場合は操作できる状態に戻す
                ViewController.View.UserInteraction = true;
                HomeViewDelegate.HideTapBlock(false, 0f);
            });
        }

        void ShowLimitView()
        {
            MessageViewUtil.ShowMessageWithClose("確認",
                "本日分のコイン獲得クエストへの挑戦可能回数が0回となりましたので挑戦できません。");
        }


        void IEnhanceQuestTopViewDelegate.OnAdChallengeButtonTapped()
        {
            if (!_model.AdChallengeCount.IsEnough())
            {
                ShowLimitView();
                return;
            }

            // 広告を表示
            if (GetHeldAdSkipPassInfoUseCase.GetHeldAdSkipPassInfo().IsEmpty())
            {
                var vc = CreateInAppAdvertisingConfirmView();
                ViewController.PresentModally(vc);
            }
            else
            {
                ViewController.View.UserInteraction = false;
                HomeViewDelegate.ShowTapBlock(false, null, 0f);

                // ステージ開始
                StartStageWireFrame.StartStage(ViewController.View, _model.MstStageId, StaminaBoostCount.One, didStart =>
                    {
                        if (didStart)
                        {
                            LocalNotificationScheduler.RefreshRemainCoinQuestSchedule();
                            return;
                        }

                        // NOTE: 開始できない場合は操作できる状態に戻す
                        ViewController.View.UserInteraction = true;
                        HomeViewDelegate.HideTapBlock(false, 0f);
                    },
                    true);
            }
        }

        InAppAdvertisingConfirmViewController CreateInAppAdvertisingConfirmView()
        {
            //広告表示
            var vc = ViewFactory.Create<InAppAdvertisingConfirmViewController>();
            vc.SetUp(
                IAARewardFeatureType.QuestChallenge,
                string.Empty, //未使用
                1,
                _model.AdChallengeCount.Value,
                () =>
                {
                    ViewController.View.UserInteraction = false;
                    HomeViewDelegate.ShowTapBlock(false, null, 0f);

                    DoAsync.Invoke(ViewController.View, async ct =>
                    {
                        var result =
                            await InAppAdvertisingWireframe.ShowAdAsync(IAARewardFeatureType.QuestChallenge, ct);

                        if (result == AdResultType.Cancelled)
                        {
                            // NOTE: 開始できない場合は操作できる状態に戻す
                            ViewController.View.UserInteraction = true;
                            HomeViewDelegate.HideTapBlock(false, 0f);
                            return;
                        }

                        //ステージ開始
                        StartStageWireFrame.StartStage(ViewController.View, _model.MstStageId, StaminaBoostCount.One, didStart =>
                            {
                                if (didStart)
                                {
                                    LocalNotificationScheduler.RefreshRemainCoinQuestSchedule();
                                    return;
                                }

                                // NOTE: 開始できない場合は操作できる状態に戻す
                                ViewController.View.UserInteraction = true;
                                HomeViewDelegate.HideTapBlock(false, 0f);
                            },
                            true);
                    });
                }
            );
            return vc;
        }

        void IEnhanceQuestTopViewDelegate.OnPartyFormationButtonTapped()
        {
            SetPartyFormationEventBonusUseCase.SetEnhanceQuestBonus(_model.MstQuestId);
            var argument = new HomePartyFormationViewController.Argument(
                _model.MstStageId,
                InGameContentType.Stage,
                EventBonusGroupId.Empty,
                _model.MstQuestId);
            var controller = ViewFactory.Create<
                HomePartyFormationViewController,
                HomePartyFormationViewController.Argument>(argument);
            HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
        }

        void IEnhanceQuestTopViewDelegate.OnBonusUnitButtonTapped()
        {
            var argument = new EventBonusUnitListViewController.Argument(
                EventBonusGroupId.Empty,
                _model.MstQuestId,
                QuestType.Enhance);
            var controller = ViewFactory.Create<
                EventBonusUnitListViewController,
                EventBonusUnitListViewController.Argument>(argument);
            ViewController.PresentModally(controller);
        }

        void IEnhanceQuestTopViewDelegate.OnBackButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }

        void IEnhanceQuestTopViewDelegate.OnInfoButtonTapped()
        {
            var controller = ViewFactory.Create<EnhanceQuestScoreDetailViewController>();
            HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
        }

        void IEnhanceQuestTopViewDelegate.OnHelpButtonTapped()
        {
            var functionName = HelpDialogIdDefinitions.EnhanceQuest;
            TutorialTipDialogViewWireFrame.ShowTutorialTipDialog(ViewController, functionName);
        }

        EnhanceQuestTopViewModel Translate(EnhanceQuestTopModel model)
        {
            return new EnhanceQuestTopViewModel(
                model.HighScore,
                model.NextThresholdScore,
                model.NextThresholdRewardAmount,
                model.ChallengeCount,
                model.AdChallengeCount,
                model.TotalBonusPercentage,
                model.PartyName,
                HeldAdSkipPassInfoViewModelTranslator.ToHeldAdSkipPassInfoViewModel(
                    model.HeldAdSkipPassInfoModel),
                model.CampaignModels.Select(CampaignViewModelTranslator.ToCampaignViewModel).ToList());
        }

        void ShowTutorialDialogIfNeed()
        {
            // チュートリアルが完了している場合は早期リターン
            var tutorialFunctionName = TutorialFreePartIdDefinitions.TransitEnhanceQuest;
            if (CheckFreePartTutorialCompletedUseCase.CheckFreePartTutorialCompleted(tutorialFunctionName))
            {
                return;
            }

            TutorialTipDialogViewWireFrame.ShowTutorialTipDialog(ViewController, tutorialFunctionName);

            // チュートリアル完了処理
            DoAsync.Invoke(ViewController.ActualView.destroyCancellationToken, async cancellationToken =>
            {
                await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(cancellationToken, tutorialFunctionName);
            });
        }
    }
}

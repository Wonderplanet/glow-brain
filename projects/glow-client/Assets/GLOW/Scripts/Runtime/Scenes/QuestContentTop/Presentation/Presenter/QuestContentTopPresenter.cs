using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.Tutorial.Domain.Context;
using GLOW.Scenes.AdventBattle.Presentation.Presenter;
using GLOW.Scenes.AdventBattle.Presentation.View;
using GLOW.Scenes.AdventBattleRanking.Presentation.Translators;
using GLOW.Scenes.AdventBattleRanking.Presentation.Views;
using GLOW.Scenes.EnhanceQuestTop.Presentation.Views;
using GLOW.Scenes.EventQuestSelect.Domain;
using GLOW.Scenes.EventQuestSelect.Presentation;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.PvpTop.Domain;
using GLOW.Scenes.PvpTop.Presentation;
using GLOW.Scenes.QuestContentTop.Domain;
using GLOW.Scenes.QuestContentTop.Domain.enums;
using GLOW.Scenes.QuestContentTop.Presentation.WireFrame;
using WonderPlanet.ToastNotifier;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Domain.Modules;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.QuestContentTop.Presentation
{
    public class QuestContentTopPresenter : IQuestContentTopViewDelegate
    {
        [Inject] QuestContentTopViewController ViewController { get; }
        [Inject] QuestContentTopUseCase UseCase { get; }
        [Inject] EventQuestWireFrame EventQuestWireFrame { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] GetRecentAdventBattleRankingUseCase GetRecentAdventBattleRankingUseCase { get; }
        [Inject] AdventBattleWireFrame AdventBattleWireFrame { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] IHomeFooterDelegate HomeFooterDelegate { get; }
        [Inject] IBackgroundMusicPlayable BackgroundMusicPlayable { get; }
        [Inject] CheckPvpOpenUseCase CheckPvpOpenUseCase { get; }
        [Inject] CheckOpenAdventBattleUseCase CheckOpenAdventBattleUseCase { get; }
        [Inject] EventOpenCheckUseCase EventOpenCheckUseCase { get; }
        [Inject] IFreePartTutorialPlayingStatus FreePartTutorialPlayingStatus { get; }
        [Inject] ITutorialFreePartContext FreePartContext { get; }
        [Inject] CheckContentMaintenanceUseCase CheckContentMaintenanceUseCase { get; }
        [Inject] ContentMaintenanceWireframe ContentMaintenanceWireframe { get; }
        [Inject] UpdateBadgeForContentTopUseCase UpdateBadgeForContentTopUseCase { get; }
        
        void IQuestContentTopViewDelegate.OnViewWillAppear()
        {
            // コンテンツトップBGM再生
            BackgroundMusicPlayable.Play(BGMAssetKeyDefinitions.BGM_quest_content_top);

            EventQuestWireFrame.SubscribeContentTopViewController(ViewController);
            Refresh();

            UpdateEventMissionBadge();
        }

        void IQuestContentTopViewDelegate.OnViewDidUnload()
        {
            EventQuestWireFrame.UnsubscribeContentTopViewController();
        }

        public void Refresh()
        {
            var model = UseCase.UpdateAndGetQuestContentTopUseCaseModel();
            ViewController.SetViewModel(QuestContentTopViewModelTranslator.Translate(model));
            HomeFooterDelegate.UpdateBadgeStatus();
        }

        void IQuestContentTopViewDelegate.OnEventButtonTapped(MasterDataId mstEventId)
        {
            if (!EventOpenCheckUseCase.IsOpenEvent(mstEventId))
            {
                EventQuestWireFrame.BackToHomeTop();
                return;
            }

            var argument = new EventQuestSelectViewController.Argument(mstEventId);
            var viewController = ViewFactory.Create<
                EventQuestSelectViewController,
                EventQuestSelectViewController.Argument>(argument);
            HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap);
        }
        void IQuestContentTopViewDelegate.OnEnhanceButtonTapped()
        {
            if (IsContentMaintenance(ContentMaintenanceType.EnhanceQuest)) return;
            var enhanceQuestTopViewController = ViewFactory.Create<EnhanceQuestTopViewController>();
            HomeViewNavigation.TryPush(enhanceQuestTopViewController, HomeContentDisplayType.BottomOverlap);
        }

        void IQuestContentTopViewDelegate.OnRaidButtonTapped()
        {
            if (IsContentMaintenance(ContentMaintenanceType.AdventBattle)) return;
            // 降臨バトルが開催中かを判定する
            var isOpenAdventBattle = CheckOpenAdventBattleUseCase.IsOpen();
            if (!isOpenAdventBattle)
            {
                AdventBattleWireFrame.ShowCloseMessage(Refresh);
                return;
            }

            var adventBattleTopViewController = ViewFactory.Create<AdventBattleTopViewController>();
            HomeViewNavigation.TryPush(adventBattleTopViewController, HomeContentDisplayType.BottomOverlap);
        }

        void IQuestContentTopViewDelegate.OnLimitedButtonTapped()
        {
            Toast.MakeText("not implemented").Show();
        }

        void IQuestContentTopViewDelegate.OnPvpButtonTapped()
        {
            if (IsContentMaintenance(ContentMaintenanceType.Pvp)) return;
            var pvpOpeningStatusModel = CheckPvpOpenUseCase.GetModel();

            if (pvpOpeningStatusModel.OpeningStatusAtTimeType ==
                QuestContentOpeningStatusAtTimeType.Totalizing)
            {
                CommonToastWireFrame.ShowScreenCenterToast(
                    "現在ランキング結果の集計中になります\n集計終了までお待ちください");
                return;
            }

            if (pvpOpeningStatusModel.OpeningStatusAtTimeType != QuestContentOpeningStatusAtTimeType.Opening)
            {
                CommonToastWireFrame.ShowScreenCenterToast(
                    "現在、ランクマッチは\n開催されておりません。");
                return;
            }
            if (pvpOpeningStatusModel.OpeningStatusAtUserStatus == QuestContentOpeningStatusAtUserStatus.StageLocked ||
                pvpOpeningStatusModel.OpeningStatusAtUserStatus == QuestContentOpeningStatusAtUserStatus.RankLocked)
            {
                CommonToastWireFrame.ShowScreenCenterToast(
                    "参加にはランクマッチの\n参加条件を満たす必要があります。");
                return;
            }

            var viewController = ViewFactory.Create<PvpTopViewController>();
            HomeViewNavigation.TryPush(viewController, HomeContentDisplayType.BottomOverlap);
        }

        void IQuestContentTopViewDelegate.OnRankingButtonTapped()
        {
            if (IsContentMaintenance(ContentMaintenanceType.AdventBattle)) return;
            DoAsync.Invoke(
                ViewController.ActualView,
                ScreenInteractionControl,
                async cancellationToken =>
                {
                    var rankingModel =
                        await GetRecentAdventBattleRankingUseCase.GetRecentAdventBattleRanking(cancellationToken);
                    var viewModel = AdventBattleRankingViewModelTranslator.ToViewModel(rankingModel);
                    var argument = new AdventBattleRankingViewController.Argument(viewModel);
                    AdventBattleWireFrame.ShowRankingView(argument);
            });
        }

        bool IsContentMaintenance(ContentMaintenanceType contentMaintenanceType)
        {
            if (CheckContentMaintenanceUseCase.IsInMaintenance(
                    new[] { new ContentMaintenanceTarget(
                            contentMaintenanceType,
                            MasterDataId.Empty) }))
            {
                // チュートリアル再生中であれば、チュートリアルを終了する
                if (FreePartTutorialPlayingStatus.IsPlayingTutorialSequence)
                {
                    FreePartContext.InterruptTutorial();
                }
                ContentMaintenanceWireframe.ShowDialog();
                return true;
            }
            return false;
        }
        
        void UpdateEventMissionBadge()
        {
            DoAsync.Invoke(ViewController.ActualView.destroyCancellationToken, async cancellationToken =>
            {
                var updatedEventBadgeDictionary = await UpdateBadgeForContentTopUseCase.UpdateBadgeAndMaintenance(
                    cancellationToken);
                ViewController.SetEventMissionBadge(updatedEventBadgeDictionary);
            });
        }
    }
}

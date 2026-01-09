using System;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.EventMission.Presentation.Facade;
using GLOW.Scenes.EventQuestSelect.Domain;
using GLOW.Scenes.EventQuestSelect.Domain.UseCase;
using GLOW.Scenes.EventQuestTop.Domain.UseCases;
using GLOW.Scenes.EventQuestTop.Presentation.Translators;
using GLOW.Scenes.EventQuestTop.Presentation.ViewModels;
using GLOW.Scenes.EventQuestTop.Presentation.Views;
using GLOW.Scenes.ExchangeShop.Presentation.View;
using GLOW.Scenes.Home.Domain.AssetLoader;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Presenters;
using GLOW.Scenes.Home.Presentation.Translator;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views.HomeStageInfoView;
using GLOW.Scenes.HomePartyFormation.Presentation.Views;
using GLOW.Scenes.InGameSpecialRule.Presentation.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Presentation.Views;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.QuestContentTop.Presentation.WireFrame;
using UIKit;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.EventQuestTop.Presentation.Presenters
{
    /// <summary>
    /// 42_イベントステージ
    /// 　42-1_イベントクエスト
    /// 　　42-1-3_イベントクエストステージ選択
    /// </summary>
    public class EventQuestTopPresenter : IEventQuestTopViewDelegate
    {
        [Inject] EventQuestTopUseCase UseCase { get; }
        [Inject] EventStageSelectUseCase EventStageSelectUseCase { get; }
        [Inject] EventMissionBadgeUseCase EventMissionBadgeUseCase { get; }
        [Inject] EventQuestTopViewController ViewController { get; }
        [Inject] EventQuestTopViewController.Argument Argument { get; }
        [Inject] EventQuestWireFrame EventQuestWireFrame { get; }
        [Inject] HomeViewController HomeViewController { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] HomeStageInfoUseCases HomeStageInfoUseCases { get; }
        [Inject] HomeStageInfoViewModelFactory HomeStageInfoViewModelFactory { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IEventMissionWireFrame EventMissionWireFrame { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] GetCurrentPartyNameUseCase GetCurrentPartyNameUseCase { get; }
        [Inject] IStageSelectViewDelegate StageSelectViewDelegate { get; }
        [Inject] IBackGroundSpriteLoader BackGroundSpriteLoader { get; }
        [Inject] EventExchangeShopCheckUseCase EventExchangeShopCheckUseCase { get; }
        [Inject] EventOpenCheckUseCase EventOpenCheckUseCase { get; }


        DateTimeOffset _questEndAt;
        MasterDataId _mstEventId;
        MasterDataId _mstQuestGroupId;
        MasterDataId _mstExchangeId;

        void IEventQuestTopViewDelegate.OnViewDidLoad()
        {
            var model = UseCase.UpdateAndGetModel(Argument.MstQuestGroupId);
            _questEndAt = model.QuestEndAt;
            _mstEventId = model.MstEventId;
            _mstQuestGroupId = model.MstQuestGroupId;


            ViewController.Initialize(
                    EventQuestTopViewModelTranslator.Translate(model),
                    ViewController.View.GetCancellationTokenOnDestroy())
                .Forget();

            _mstExchangeId = EventExchangeShopCheckUseCase.GetEventExchangeShop(_mstEventId);
            ViewController.SetEventExchangeShopActive(!_mstExchangeId.IsEmpty());
        }

        void IEventQuestTopViewDelegate.OnViewWillAppear()
        {
            UpdatePartyName();
            UpdateMissionBadge();
        }

        void IEventQuestTopViewDelegate.OnViewDidUnload()
        {
            EventQuestWireFrame.UnsubscribeEventStageSelectViewController();
            BackGroundSpriteLoader.Unload();
        }

        void IEventQuestTopViewDelegate.OnBackButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }

        void UpdateMissionBadge()
        {
            var unReceiveMissionReward = EventMissionBadgeUseCase.GetEventMissionBadge(_mstEventId);
            ViewController.SetMissionBadge(unReceiveMissionReward);
        }

        void IEventQuestTopViewDelegate.OnStageInfoButtonTapped(MasterDataId mstStageId)
        {
            // 期限超えていたらクエストコンテンツTOPに戻す
            if (IsOverQuestEndTime())
            {
                EventQuestWireFrame.BackToHomeTopAfterQuestEnded();
                return;
            }

            //HomeMainPresenterと重複。必要あれば統合
            ShowHomeStageInfoView(mstStageId);
        }

        void IEventQuestTopViewDelegate.OnQuestUnReleasedClicked(
            StageReleaseRequireSentence stageReleaseRequireSentence)
        {
            CommonToastWireFrame.ShowScreenCenterToast(stageReleaseRequireSentence.Value);
        }

        void IEventQuestTopViewDelegate.OnMissionButtonTapped()
        {
            // 期限超えていたらクエストコンテンツTOPに戻す
            if (IsOverQuestEndTime())
            {
                EventQuestWireFrame.BackToHomeTopAfterQuestEnded();
                return;
            }

            EventMissionWireFrame.ShowEventMissionViewInEvent(ViewController, _mstEventId, UpdateMissionBadge);
        }

        void IEventQuestTopViewDelegate.OnPartyEditButtonTapped(MasterDataId mstStageId)
        {
            // 期限超えていたらクエストコンテンツTOPに戻す
            if (IsOverQuestEndTime())
            {
                EventQuestWireFrame.BackToHomeTopAfterQuestEnded();
                return;
            }

            var argument = new HomePartyFormationViewController.Argument(
                mstStageId,
                InGameContentType.Stage,
                EventBonusGroupId.Empty,
                MasterDataId.Empty);
            var controller = ViewFactory
                .Create<HomePartyFormationViewController, HomePartyFormationViewController.Argument>(argument);
            HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
        }

        void IEventQuestTopViewDelegate.OnInGameSpecialRuleTapped(MasterDataId selectedMstStageRuleGroupId)
        {
            var controller = ViewFactory
                .Create<InGameSpecialRuleViewController, InGameSpecialRuleViewController.Argument>(
                    new InGameSpecialRuleViewController.Argument(
                        selectedMstStageRuleGroupId,
                        InGameContentType.Stage,
                        InGameSpecialRuleFromUnitSelectFlag.False));
            ViewController.PresentModally(controller);
        }

        void IEventQuestTopViewDelegate.OnEventExchangeShopButtonTapped()
        {
            if (_mstExchangeId.IsEmpty())
            {
                CommonToastWireFrame.ShowScreenCenterToast("対象のイベント交換所はありません");
                return;
            }

            if (!EventOpenCheckUseCase.IsOpenEvent(_mstEventId))
            {
                EventQuestWireFrame.BackToHomeTop();
                return;
            }

            var argument = new ExchangeShopTopViewController.Argument(_mstExchangeId);
            var controller = ViewFactory.Create<ExchangeShopTopViewController, ExchangeShopTopViewController.Argument>(argument);
            HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
        }

        void IEventQuestTopViewDelegate.OnStageStart(
            UIViewController controller,
            EventQuestTopElementViewModel selectedElementViewModel)
        {
            // 期限超えていたらクエストコンテンツTOPに戻す
            if (IsOverQuestEndTime())
            {
                EventQuestWireFrame.BackToHomeTopAfterQuestEnded();
                return;
            }

            StageSelectViewDelegate.OnStartStageSelected(
                controller,
                selectedElementViewModel.MstStageId,
                selectedElementViewModel.EndAt,
                selectedElementViewModel.StageReleaseStatus.ToStagePlayableFlag(),
                selectedElementViewModel.StageConsumeStamina,
                selectedElementViewModel.DailyClearCount,
                selectedElementViewModel.DailyPlayableCount,
                null);

            EventStageSelectUseCase.SaveLastPlayedStageInfo(_mstQuestGroupId, selectedElementViewModel.MstStageId);
        }

        bool IsOverQuestEndTime()
        {
            return _questEndAt < TimeProvider.Now;
        }

        void UpdatePartyName()
        {
            var partyName = GetCurrentPartyNameUseCase.GetCurrentPartyName();
            ViewController.SetCurrentPartyName(partyName);
        }

        void ShowHomeStageInfoView(MasterDataId stageId)
        {
            DoAsync.Invoke(ViewController.ActualView, async ct =>
            {
                var homeStageInfoUseCaseModel = HomeStageInfoUseCases.GetHomeStageInfoUseCasesModel(stageId);
                var viewModel = HomeStageInfoViewModelFactory.ToHomeStageInfoViewModel(homeStageInfoUseCaseModel);

                // 画面がチラつくので1フレーム待つ
                await UniTask.Delay(1, cancellationToken: ct);

                var controller = ViewFactory
                    .Create<HomeStageInfoViewController, HomeStageInfoViewController.Argument>(
                        new HomeStageInfoViewController.Argument(viewModel));
                controller.ReopenStageInfoAction = () =>
                {
                    if (HomeViewController.ViewContextController.CurrentContentType == HomeContentTypes.Main
                        || HomeViewController.ViewContextController.CurrentContentType == HomeContentTypes.Content)
                    {
                        ShowHomeStageInfoView(stageId);
                    }
                };
                ViewController.PresentModally(controller);
            });
        }
    }
}

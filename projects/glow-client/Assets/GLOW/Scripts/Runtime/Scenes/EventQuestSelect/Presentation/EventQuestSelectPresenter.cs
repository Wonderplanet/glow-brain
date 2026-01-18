using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.AdventBattle.Presentation.Presenter;
using GLOW.Scenes.AdventBattle.Presentation.View;
using GLOW.Scenes.BoxGacha.Domain.UseCase;
using GLOW.Scenes.BoxGacha.Presentation.Translator;
using GLOW.Scenes.BoxGacha.Presentation.View;
using GLOW.Scenes.EventMission.Presentation.Facade;
using GLOW.Scenes.EventQuestSelect.Domain;
using GLOW.Scenes.EventQuestSelect.Domain.UseCase;
using GLOW.Scenes.EventQuestTop.Domain.UseCases;
using GLOW.Scenes.ExchangeShop.Presentation.View;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.QuestContentTop.Presentation.WireFrame;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Domain.Modules;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.EventQuestSelect.Presentation
{
    /// <summary>
    /// 42_イベントステージ
    /// 　42-1_イベントクエスト
    /// 　　42-1-2_いいジャン祭トップ画面（クエスト選択画面）
    /// </summary>
    public class EventQuestSelectPresenter : IEventQuestSelectViewDelegate
    {
        [Inject] EventQuestListUseCase UseCase { get; }
        [Inject] EventOpenCheckUseCase EventOpenCheckUseCase { get; }
        [Inject] EventQuestOpenCheckUseCase EventQuestOpenCheckUseCase { get; }
        [Inject] EventQuestSelectViewController ViewController { get; }
        [Inject] EventQuestSelectViewController.Argument Argument { get; }
        [Inject] EventQuestWireFrame EventQuestWireFrame { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IBackgroundMusicPlayable BackgroundMusicPlayable { get; }
        [Inject] EventMissionBadgeUseCase EventMissionBadgeUseCase { get; }
        [Inject] IEventMissionWireFrame EventMissionWireFrame { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] OpenAdventBattleGetUseCase OpenAdventBattleGetUseCase { get; }
        [Inject] AdventBattleWireFrame AdventBattleWireFrame { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] CheckContentMaintenanceUseCase CheckContentMaintenanceUseCase { get; }
        [Inject] ContentMaintenanceWireframe ContentMaintenanceWireframe { get; }
        [Inject] EventExchangeShopCheckUseCase EventExchangeShopCheckUseCase { get; }
        [Inject] FetchBoxGachaInfoUseCase FetchBoxGachaInfoUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }

        DateTimeOffset _eventEndAt;
        MasterDataId _mstAdventBattleId;
        MasterDataId _mstExchangeId;

        void IEventQuestSelectViewDelegate.OnViewDidLoad()
        {
            // コンテンツトップBGM再生
            BackgroundMusicPlayable.Play(BGMAssetKeyDefinitions.BGM_quest_content_top);
            var vm = EventQuestSelectViewModelTranslator.Translate(UseCase.GetModel(Argument.MstEventId));
            _eventEndAt = vm.EventEndAt;
            _mstAdventBattleId = vm.MstAdventBattleId;

            _mstExchangeId = EventExchangeShopCheckUseCase.GetEventExchangeShop(Argument.MstEventId);
            ViewController.SetEventExchangeShopActive(!_mstExchangeId.IsEmpty());

            ViewController.SetUpView(vm);
            EventQuestWireFrame.SubscribeEventQuestSelectViewController(ViewController);
        }

        void IEventQuestSelectViewDelegate.OnViewDidUnload()
        {
            EventQuestWireFrame.UnsubscribeEventQuestSelectViewController();
        }

        void IEventQuestSelectViewDelegate.OnEventQuestButtonTapped(MasterDataId mstQuestGroupId)
        {
            if (!EventQuestOpenCheckUseCase.IsOpenEventQuest(mstQuestGroupId))
            {
                EventQuestWireFrame.BackToHomeTopAfterQuestEnded();
                return;
            }

            if (!EventOpenCheckUseCase.IsOpenEvent(Argument.MstEventId))
            {
                EventQuestWireFrame.BackToHomeTop();
                return;
            }

            EventQuestWireFrame.CreateEventStageSelectView(mstQuestGroupId);
        }

        void IEventQuestSelectViewDelegate.OnBackButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }

        void IEventQuestSelectViewDelegate.OnAdventBattleButtonTapped()
        {
            if (CheckContentMaintenanceUseCase.IsInMaintenance(ContentMaintenanceTarget.AdventBattle))
            {
                ContentMaintenanceWireframe.ShowDialog();
                return;
            }

            var mst = OpenAdventBattleGetUseCase.GetOpenAdventBattle(_mstAdventBattleId);
            if (mst.IsEmpty())
            {
                AdventBattleWireFrame.ShowCloseMessage(null);
                return;
            }
            var adventBattleTopViewController = ViewFactory.Create<AdventBattleTopViewController>();
            HomeViewNavigation.TryPush(adventBattleTopViewController, HomeContentDisplayType.BottomOverlap);
        }

        void IEventQuestSelectViewDelegate.UpdateMissionBadge()
        {
            UpdateMissionBadge();
        }

        void IEventQuestSelectViewDelegate.OnMissionButtonTapped()
        {
            if (_eventEndAt < TimeProvider.Now)
            {
                EventQuestWireFrame.BackToHomeTop();
                return;
            }

            EventMissionWireFrame.ShowEventMissionViewInEvent(ViewController, Argument.MstEventId, UpdateMissionBadge);
        }

        void IEventQuestSelectViewDelegate.ShowEventExchangeShop()
        {
            if (_mstExchangeId.IsEmpty())
            {
                CommonToastWireFrame.ShowScreenCenterToast("対象のイベント交換所はありません");
                return;
            }

            if (!EventOpenCheckUseCase.IsOpenEvent(Argument.MstEventId))
            {
                EventQuestWireFrame.BackToHomeTop();
                return;
            }

            var argument = new ExchangeShopTopViewController.Argument(_mstExchangeId);
            var controller = ViewFactory.Create<ExchangeShopTopViewController, ExchangeShopTopViewController.Argument>(argument);
            HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
        }
        
        void IEventQuestSelectViewDelegate.OnBoxGachaButtonTapped()
        {
            if (!EventOpenCheckUseCase.IsOpenEvent(Argument.MstEventId))
            {
                EventQuestWireFrame.BackToHomeTop();
                return;
            }
            
            DoAsync.Invoke(ViewController.ActualView, ScreenInteractionControl, async cancellationToken =>
            {
                var boxGachaModel = await FetchBoxGachaInfoUseCase.CacheAndShowBoxGachaInfo(
                    Argument.MstEventId,
                    cancellationToken);
                var viewModel = BoxGachaTopViewModelTranslator.ToBoxGachaTopViewModel(boxGachaModel);
                var argument = new BoxGachaTopViewController.Argument(viewModel, Argument.MstEventId);
                
                var controller = ViewFactory.Create<
                    BoxGachaTopViewController, 
                    BoxGachaTopViewController.Argument>(argument);
                HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);
            });
        }

        void UpdateMissionBadge()
        {
            ViewController.SetMissionBadge(EventMissionBadgeUseCase.GetEventMissionBadge(Argument.MstEventId));
        }
    }
}

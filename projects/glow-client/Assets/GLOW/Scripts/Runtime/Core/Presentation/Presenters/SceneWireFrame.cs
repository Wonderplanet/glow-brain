using System;
using System.Collections.Generic;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Scenes.AdventBattle.Presentation.View;
using GLOW.Scenes.EnhanceQuestTop.Presentation.Views;
using GLOW.Scenes.EventQuestSelect.Presentation;
using GLOW.Scenes.EventQuestTop.Presentation.Views;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.PvpTop.Presentation;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Core.Presentation.Presenters
{
    public class SceneWireFrame
    {
        [Inject] SceneWireFrameUseCase UseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] HomeViewController HomeViewController { get; }

        public SceneWireFrameViewModel GetInitialViewControllers()
        {
            // 保存されているCategoryを見て遷移先をFactory.Createする
            var model = UseCase.Get();
            return model.Category switch
            {
                SceneViewContentCategory.None => GetHomeTop(),
                SceneViewContentCategory.MainStage => GetHomeTop(),
                SceneViewContentCategory.EventStage => GetEventStage(model),
                SceneViewContentCategory.EnhanceStage => GetEnhanceStage(),
                SceneViewContentCategory.AdventBattle => GetAdventBattleTop(model),
                SceneViewContentCategory.Pvp => GetPvpTop(model),
                _ => throw new NotImplementedException("No define view type : " + model.Category)
            };
        }

        SceneWireFrameViewModel GetHomeTop()
        {
            var firstViewCon = HomeViewController.ViewContextController.HomeMainViewController;
            var vcs = new List<(UIViewController vc, HomeContentDisplayType showType)>()
            {
                (firstViewCon.ViewController, firstViewCon.HomeContentDisplayType)
            };
            return new SceneWireFrameViewModel(vcs, HomeContentTypes.Main);
        }

        SceneWireFrameViewModel GetEventStage(SceneWireFrameUseCaseModel model)
        {
            var firstViewCon = HomeViewController.ViewContextController.QuestContentTopViewController;
            if (!model.IsOpenEvent())
            {
                // イベント開催終了以降
                return GetHomeTop();
            }
            else if (model.OpenStatus != QuestOpenStatus.Released)
            {
                //1日1回とかで遊べるクエスト無くなったとき
                var vcs = new List<(UIViewController vc, HomeContentDisplayType showType)>()
                {
                    (firstViewCon.ViewController, firstViewCon.HomeContentDisplayType),
                    (ViewFactory.Create<EventQuestSelectViewController, EventQuestSelectViewController.Argument>(new EventQuestSelectViewController.Argument(model.MstEventId)), HomeContentDisplayType.BottomOverlap),
                };
                return new SceneWireFrameViewModel(vcs, HomeContentTypes.Content);
            }
            else
            {
                // イベント開催中
                var eventQuestTopViewController = ViewFactory.Create<EventQuestTopViewController, EventQuestTopViewController.Argument>(new EventQuestTopViewController.Argument(model.MstId));
                eventQuestTopViewController.HideLoadingView = true;// trueにしないとEventQuestWireFrameの処理が通らず、開放アニメーション再生されないので細工する
                var vcs = new List<(UIViewController vc, HomeContentDisplayType showType)>()
                {
                    (firstViewCon.ViewController, firstViewCon.HomeContentDisplayType),
                    (ViewFactory.Create<EventQuestSelectViewController, EventQuestSelectViewController.Argument>(new EventQuestSelectViewController.Argument(model.MstEventId)), HomeContentDisplayType.BottomOverlap),
                    (eventQuestTopViewController, HomeContentDisplayType.BottomOverlap),
                };
                return new SceneWireFrameViewModel(vcs, HomeContentTypes.Content);
            }
        }


        SceneWireFrameViewModel GetAdventBattleTop(SceneWireFrameUseCaseModel model)
        {
            if (!model.IsOpenAdventBattle())
            {
                // AdventBattle開催終了以降
                return GetHomeTop();
            }

            var firstViewCon = HomeViewController.ViewContextController.QuestContentTopViewController;
            var vcs = new List<(UIViewController vc, HomeContentDisplayType showType)>()
            {
                (firstViewCon.ViewController, firstViewCon.HomeContentDisplayType),
                (ViewFactory.Create<AdventBattleTopViewController>(), HomeContentDisplayType.BottomOverlap)
            };
            return new SceneWireFrameViewModel(vcs, HomeContentTypes.Content);
        }

        SceneWireFrameViewModel GetEnhanceStage()
        {
            var firstViewCon = HomeViewController.ViewContextController.QuestContentTopViewController;
            var vcs = new List<(UIViewController vc, HomeContentDisplayType showType)>()
            {
                (firstViewCon.ViewController, firstViewCon.HomeContentDisplayType),
                (ViewFactory.Create<EnhanceQuestTopViewController>(), HomeContentDisplayType.BottomOverlap)
            };
            return new SceneWireFrameViewModel(vcs, HomeContentTypes.Content);
        }

        SceneWireFrameViewModel GetPvpTop(SceneWireFrameUseCaseModel model)
        {
            var firstViewCon = HomeViewController.ViewContextController.QuestContentTopViewController;
            if (model.IsOpenPvp())
            {
                var vcs = new List<(UIViewController vc, HomeContentDisplayType showType)>()
                {
                    (firstViewCon.ViewController, firstViewCon.HomeContentDisplayType),
                    (ViewFactory.Create<PvpTopViewController>(), HomeContentDisplayType.BottomOverlap)
                };
                return new SceneWireFrameViewModel(vcs, HomeContentTypes.Content);
            }
            else
            {
                // PVP開催期間外対応
                return GetHomeTop();
            }
        }
    }
}

using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.StaminaRecover;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.ItemDetail.Presentation.Views;
using UIKit;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.ItemDetail.Presentation.Presenters
{
    public class ItemDetailTransitionWireFrame
    {
        [Inject] IHomeViewControl HomeViewControl { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }

        public void Transit(ItemDetailEarnLocationViewModel earnLocationViewModel)
        {
            switch (earnLocationViewModel.TransitionType)
            {
                case ItemTransitionType.MainQuest:
                    OnTransitionMainQuest(
                        earnLocationViewModel.MasterDataId,
                        earnLocationViewModel.TransitionPossibleFlag);
                    break;
                case ItemTransitionType.EventQuest:
                    OnTransitionEventQuest(
                        earnLocationViewModel.MasterDataId,
                        earnLocationViewModel.TransitionPossibleFlag);
                    break;
                case ItemTransitionType.ShopItem:
                    OnTransitionShop(
                        ShopContentTypes.Shop,
                        earnLocationViewModel.MasterDataId,
                        earnLocationViewModel.TransitionPossibleFlag);
                    break;
                case ItemTransitionType.Pack:
                    OnTransitionShop(
                        ShopContentTypes.Pack,
                        earnLocationViewModel.MasterDataId,
                        earnLocationViewModel.TransitionPossibleFlag);
                    break;
                case ItemTransitionType.Achievement:
                    OnTransitionMission(MissionType.Achievement);
                    break;
                case ItemTransitionType.LoginBonus:
                    OnTransitionMission(MissionType.DailyBonus);
                    break;
                case ItemTransitionType.DailyMission:
                    OnTransitionMission(MissionType.Daily);
                    break;
                case ItemTransitionType.WeeklyMission:
                    OnTransitionMission(MissionType.Weekly);
                    break;
                case ItemTransitionType.Patrol:
                    OnTransitionExploration();
                    break;
                case ItemTransitionType.ExchangeShop:
                    OnTransitionExchangeShop(earnLocationViewModel.MasterDataId);
                    break;
            }
        }

        public void OnTransitionMainQuest(
            MasterDataId masterDataId,
            TransitionPossibleFlag transitionPossibleFlag,
            bool popBeforeDetail = false)
        {
            if (masterDataId.IsEmpty() || transitionPossibleFlag)
            {
                CloseAllModally();
                // メインクエストへ遷移
                HomeViewControl.OnQuestSelectedFromHome(masterDataId, popBeforeDetail);
            }
            else
            {
                ShowMessage("未開放クエストです。メインクエストを進めてみよう。");
            }
        }

        public void OnTransitionEventQuest(
            MasterDataId masterDataId,
            TransitionPossibleFlag transitionPossibleFlag)
        {
            if (masterDataId.IsEmpty() || transitionPossibleFlag)
            {
                CloseAllModally();
                // イベントクエストへ遷移
                HomeViewControl.OnEventQuestSelectedFromHome(masterDataId);
            }
            else
            {
                ShowMessage("イベントは終了しました。次回開催をお待ちください。");
            }
        }

        public void OnTransitionShop(
            ShopContentTypes shopContentType,
            MasterDataId masterDataId,
            TransitionPossibleFlag transitionPossibleFlag)
        {
            if (masterDataId.IsEmpty() || transitionPossibleFlag)
            {
                CloseAllModally();
                // ショップへ遷移
                HomeViewControl.OnShopSelectedFromHome(shopContentType, masterDataId);
            }
            else
            {
                ShowMessage("こちらの商品は現在販売を終了しております。");
            }
        }

        public void OnTransitionMission(MissionType missionType)
        {
            CloseAllModally();
            // ミッションへ遷移
            HomeViewControl.OnNormalMissionSelectedFromHome(missionType, true);
        }

        public void OnTransitionExploration()
        {
            CloseAllModally();
            // 探索へ遷移
            HomeViewControl.OnIdleIncentiveTopSelected();
        }

        public void OnTransitionExchangeShop(MasterDataId mstExchangeId)
        {
            CloseAllModally();

            if (mstExchangeId.IsEmpty())
            {
                HomeViewControl.OnExchangeContentTopSelected();
                return;
            }
            HomeViewControl.OnExchangeShopTopSelected(mstExchangeId);
        }

        void ShowMessage(string message)
        {
            // 汎用ダイアログ 表示確認のため
            CommonToastWireFrame.ShowScreenCenterToast(message);
        }

        void CloseAllModally()
        {
            //TODO:共通化したいけど、一旦メソッドとして追加
            var a = UICanvas.Canvases
                .Select(c => c.RootViewController)
                .Where(vc => vc is WPFramework.Presentation.Views.ModalItemHostingController)
                .Where(vc => vc.ChildViewControllers.Any())
                .Select(vc => vc.ChildViewControllers[0])
                .ToList();
            foreach (var b in a)
            {
                b.Dismiss();
            }
        }
    }
}

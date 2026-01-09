using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.EventQuestSelect.Presentation;
using GLOW.Scenes.EventQuestTop.Presentation.Views;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.QuestContentTop.Presentation.WireFrame
{
    public class EventQuestWireFrame
    {
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }

        QuestContentTopViewController _topViewController;
        EventQuestSelectViewController _eventQuestSelectViewController;
        EventQuestTopViewController _eventQuestTopViewController;

        public void SubscribeContentTopViewController(QuestContentTopViewController vc)
        {
            _topViewController = vc;
        }

        public void UnsubscribeContentTopViewController()
        {
            _topViewController = null;
        }

        public void SubscribeEventQuestSelectViewController(EventQuestSelectViewController vc)
        {
            _eventQuestSelectViewController = vc;
        }

        public void UnsubscribeEventQuestSelectViewController()
        {
            _eventQuestSelectViewController = null;
        }


        // 注意：InGame > Homeに戻るときのコンテキスト復帰には使わない
        // HomeViewNavigation.HasRunningViewNavigationCoroutine()で早期returnして欲しくないのにされる可能性あり
        public void CreateEventStageSelectView(MasterDataId mstQuestGroupId)
        {
            // 画面遷移処理中だったら何もしない
            if (HomeViewNavigation.HasRunningViewNavigationCoroutine()) return;

            if(_eventQuestSelectViewController!= null) _eventQuestSelectViewController.View.UserInteraction= false;

            var controller =
                ViewFactory.Create<EventQuestTopViewController, EventQuestTopViewController.Argument>(
                    new EventQuestTopViewController.Argument(mstQuestGroupId));
            _eventQuestTopViewController = controller;
            DoAsync.Invoke(controller.View.GetCancellationTokenOnDestroy(), async ct =>
            {
                //1. LoadingViewを表示
                var loadVc = ShowLoadingView();
                await loadVc.ShowLoadingView(ct);

                //2. EventQuestTopViewControllerを表示
                //(HomeContentDisplayTypeとHierarchyの関係で裏で表示されているように見える)
                HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);

                //3. controllerの方でロードを待つ
                await UniTask.WaitUntil(() => controller.AssetLoadEnded, cancellationToken: ct);

                //4. LoadingViewを非表示にする
                await loadVc.OutLoadingView(ct);
                loadVc.DismissLoadingView();

                //5. controllerでステージ開放演出を行う
                controller.HideLoadingView = true;
                if (_eventQuestSelectViewController != null) _eventQuestSelectViewController.View.UserInteraction = true;
            });
        }

        EventQuestTopLoadingViewController ShowLoadingView()
        {
            var loadingViewController = ViewFactory.Create<EventQuestTopLoadingViewController>();
            HomeViewNavigation.PushUnmanagedView(loadingViewController, HomeContentDisplayType.FullScreenOverlap, false);
            loadingViewController.Initlialize();
            return loadingViewController;
        }

        public void UnsubscribeEventStageSelectViewController()
        {
            _eventQuestTopViewController = null;
        }

        public void BackToHomeTop()
        {
            MessageViewUtil.ShowMessageWithButton(
                "確認",
                "イベントは終了しました。\n次回開催をお待ちください。\nホーム画面に移動します。",
                "",
                "はい",
                TransitHomeTop);
        }

        public void BackToHomeTopAfterQuestEnded()
        {
            MessageViewUtil.ShowMessageWithButton(
                "確認",
                "クエストは終了しました。\nホーム画面に移動します。",
                "",
                "はい",
                TransitHomeTop);
        }

        void TransitHomeTop()
        {
            if (HomeViewNavigation.CurrentContentType == HomeContentTypes.Main)
            {
                HomeViewNavigation.TryPopToRoot();
            }
            else
            {
                HomeViewNavigation.Switch(HomeContentTypes.Main);
            }
        }
    }
}

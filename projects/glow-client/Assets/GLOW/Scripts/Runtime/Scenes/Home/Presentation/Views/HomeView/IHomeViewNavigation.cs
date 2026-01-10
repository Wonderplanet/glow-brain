using System;
using System.Collections.Generic;
using GLOW.Scenes.Home.Domain.Constants;
using UIKit;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public interface IHomeViewNavigation
    {
        HomeContentTypes CurrentContentType { get; }
        bool HasRunningViewNavigationCoroutine();
        void TryPush(UIViewController controller,HomeContentDisplayType homeContentDisplayType, bool animated = true, Action completion = null);
        void TryPop(bool animated = true, Action completion = null);
        void TryPopToRoot(bool animated = true, Action completion = null);
        void Switch(HomeContentTypes contentType, bool animated = true, Action completion = null);

        // コンテキストを作成しつつSwitchしたいときに利用する
        void SwitchMultipleViewController(IReadOnlyList<(UIViewController controller,HomeContentDisplayType viewType)> controllers, HomeContentTypes contentType, bool animated = true, Action completion = null);
        // HomeViewNavigation内でマネジメントされないViewとしてPushする
        // 前のViewの状態を維持したまま次の画面を出すため
        // View側でDismissする必要がある
        void PushUnmanagedView(UIViewController controller, HomeContentDisplayType homeContentDisplayType, bool animated = true, Action completion = null);
    }
}

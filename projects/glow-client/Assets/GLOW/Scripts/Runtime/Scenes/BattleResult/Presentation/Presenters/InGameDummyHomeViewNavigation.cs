using System;
using System.Collections.Generic;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Presentation.Views;
using UIKit;

namespace GLOW.Scenes.BattleResult.Presentation.Presenters
{
    public class InGameDummyHomeViewNavigation : IHomeViewNavigation
    {
        HomeContentTypes IHomeViewNavigation.CurrentContentType => HomeContentTypes.Main;

        bool IHomeViewNavigation.HasRunningViewNavigationCoroutine()
        {
            return false;
        }

        void IHomeViewNavigation.TryPush(
            UIViewController controller,
            HomeContentDisplayType homeContentDisplayType,
            bool animated,
            Action completion)
        {
        }

        void IHomeViewNavigation.TryPop(bool animated, Action completion)
        {
        }

        void IHomeViewNavigation.TryPopToRoot(bool animated, Action completion)
        {
        }

        void IHomeViewNavigation.Switch(
            HomeContentTypes contentType,
            bool animated,
            Action completion)
        {
        }

        void IHomeViewNavigation.SwitchMultipleViewController(
            IReadOnlyList<(UIViewController controller, HomeContentDisplayType viewType)> controllers,
            HomeContentTypes contentType,
            bool animated,
            Action completion)
        {
        }

        void IHomeViewNavigation.PushUnmanagedView(UIViewController controller, HomeContentDisplayType homeContentDisplayType, bool animated, Action completion)
        {
        }
    }
}

using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.AnnouncementWindow.Presentation.Facade;
using GLOW.Scenes.Home.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters.HomeAppearanceAction
{
    /// <summary>
    /// お知らせ表示
    /// </summary>
    public class AnnouncementAction : IHomeAppearanceAction
    {
        public class Factory : PlaceholderFactory<AnnouncementAction> { }

        [Inject] IAnnouncementViewFacade AnnouncementViewFacade { get; }
        [Inject] HomeMainViewController HomeMainViewController { get; }

        public async UniTask ExecuteAsync(HomeAppearanceActionContext context, Action onCloseCompletion,
            CancellationToken cancellationToken)
        {
            if (!context.DisplayAtLoginModel.DisplayAnnouncementFlag) return;

            await UniTask.Delay(TimeSpan.FromSeconds(0.1f), cancellationToken: cancellationToken);

            await AnnouncementViewFacade.ShowLoginAnnouncement(HomeMainViewController, cancellationToken);
        }
    }
}

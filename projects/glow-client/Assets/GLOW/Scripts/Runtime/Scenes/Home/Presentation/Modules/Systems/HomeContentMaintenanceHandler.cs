using System;
using System.Linq;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Modules.Systems;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Modules.Tutorial.Domain.Context;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Presentation.Views;
using UIKit;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Constants.Zenject;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Modules.Systems
{
    public class HomeContentMaintenanceHandler : IContentMaintenanceHandler
    {
        [Inject(Id = FrameworkInjectId.Canvas.System)] UICanvas Canvas { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }

        [Inject] IHomeViewDelegate HomeViewDelegate { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IFreePartTutorialPlayingStatus FreePartTutorialPlayingStatus { get; }
        [Inject] ITutorialFreePartContext FreePartContext { get; }
        [Inject] ContentMaintenanceWireframe ContentMaintenanceWireframe { get; }

        bool IContentMaintenanceHandler.TryHandle(bool needsCleanup, Action completion)
        {
            // ダイアログ表示
            ContentMaintenanceWireframe.ShowDialog(() =>
            {
                DoAsync.Invoke(Canvas.RootViewController.View, ScreenInteractionControl, async cancellationToken =>
                {
                    // エラー受取済み解除
                    completion?.Invoke();

                    // チュートリアル中なら終了
                    if (FreePartTutorialPlayingStatus.IsPlayingTutorialSequence)
                    {
                        FreePartContext.InterruptTutorial();
                    }

                    // モーダルを全て閉じる
                    var controllers = UICanvas.Canvases
                        .Select(c => c.RootViewController)
                        .Where(vc => vc is WPFramework.Presentation.Views.ModalItemHostingController)
                        .Where(vc => vc.ChildViewControllers.Any())
                        .Select(vc => vc.ChildViewControllers[0])
                        .ToList();
                    foreach (var vc in controllers)
                    {
                        vc.Dismiss();
                    }

                    // ダイアログ閉じるのと遷移が重なると見え方悪いのでちょっと待機
                    await UniTask.Delay(300, cancellationToken: cancellationToken);

                    // ホーム遷移
                    if (HomeViewNavigation.CurrentContentType == HomeContentTypes.Main)
                    {
                        HomeViewNavigation.TryPopToRoot();
                    }
                    else
                    {
                        HomeViewNavigation.Switch(HomeContentTypes.Main);
                    }

                    HomeViewDelegate.HideTapBlock(false, 0f);
                });
            });
            return true;
        }
    }
}

using System;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Modules.Systems;
using GLOW.Core.Presentation.Transitions;
using GLOW.Core.Presentation.Wireframe;
using GLOW.Scenes.InGame.Domain.UseCases;
using UIKit;
using WonderPlanet.SceneManagement;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Constants.Zenject;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Modules.Systems
{
    public class InGameContentMaintenanceHandler : IContentMaintenanceHandler
    {
        [Inject(Id = FrameworkInjectId.Canvas.System)] UICanvas Canvas { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }

         [Inject] ISceneNavigation SceneNavigation { get; }
        [Inject] InGameSessionCleanupUseCase InGameSessionCleanupUseCase { get; }
        [Inject] ContentMaintenanceWireframe ContentMaintenanceWireframe { get; }

        bool IContentMaintenanceHandler.TryHandle(bool needsCleanup, Action completion)
        {
            // ダイアログ表示
            ContentMaintenanceWireframe.ShowDialog(() =>
            {
                // Cleanupしない場合はダイアログ表示のみ(コンティニュー時にプリズム購入する場合考慮)
                if (!needsCleanup)
                {
                    // エラー受取済み解除
                    completion();
                    return;
                }

                DoAsync.Invoke(Canvas.RootViewController.View, ScreenInteractionControl, async cancellationToken =>
                {
                    // エラー受取済み解除
                    completion();
                    // ※部分メンテナンスが明けていた場合、ServerErrorExceptionHandlerでcatchしてタイトル遷移
                    await InGameSessionCleanupUseCase.CleanupSession(cancellationToken);

                    // ホーム遷移
                    SceneNavigation.Switch<HomeTopTransition>(default, SceneInBuildName.HOME).Forget();

                });

            });
            return true;
        }



    }
}

using System.Threading;
using Cysharp.Threading.Tasks;
using UIKit;
using WPFramework.Application.SceneDelegates;
using WPFramework.Modules.Log;
using GLOW.Scenes.Splash.Presentation.Views;
using GLOW.Scenes.Title.Applications.Delegates;
using WPFramework.Presentation.Modules;
using Zenject;

#if GLOW_DEBUG
using GLOW.Debugs.Environment.Domain;
using WPFramework.Debugs.Environment;
#endif // GLOW_DEBUG

namespace GLOW.Scenes.Splash.Application.Delegates
{
    internal sealed class SplashSceneDelegate : AbstractSceneDelegate
#if GLOW_DEBUG
        , IDebugEnvironmentSelectorInvoker
#endif // GLOW_DEBUG
    {
        [Inject] UICanvas Canvas { get; }
        [Inject] IViewFactory ViewFactory { get; }

#if GLOW_DEBUG
        [Inject] DebugEnvironmentSelector DebugEnvironmentSelector { get; }
#endif // DEBUG

        public override async UniTask Initialize(CancellationToken cancellationToken)
        {
            await base.Initialize(cancellationToken);

            ApplicationLog.Log(nameof(TitleSceneDelegate), nameof(IInitializable.Initialize));

            // NOTE: スプラッシュ画面の表示を行う
            var viewController = ViewFactory.Create<SplashViewController>();
            Canvas.RootViewController.Show(viewController, false); 
        }
        
#if GLOW_DEBUG
        async UniTask IDebugEnvironmentSelectorInvoker.Invoke(CancellationToken cancellationToken)
        {
            // NOTE: 接続環境を選択する
            //       選択するまでこのステップで待機する
            await DebugEnvironmentSelector.Invoke(cancellationToken);
        }
#endif // DEBUG
    }
}

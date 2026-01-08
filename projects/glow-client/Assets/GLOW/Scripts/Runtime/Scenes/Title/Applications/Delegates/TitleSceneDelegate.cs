using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Debugs.Command.Presentations;
using UIKit;
using WPFramework.Application.SceneDelegates;
using WPFramework.Modules.Log;
using GLOW.Scenes.Title.Presentations.Views;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Title.Applications.Delegates
{
    internal sealed class TitleSceneDelegate : AbstractSceneDelegate
    {
        [Inject] UICanvas Canvas { get; }
        [Inject] IViewFactory ViewFactory { get; }

        public override async UniTask Initialize(CancellationToken cancellationToken)
        {
            await base.Initialize(cancellationToken);

#if GLOW_DEBUG
            // NOTE: デバッグコマンドを封印する
            //       シーンによってはデバッグコマンドを有効にする必要があるためここで実行
            DebugCommandActivator.Enable();
#endif // GLOW_DEBUG

            ApplicationLog.Log(nameof(TitleSceneDelegate), nameof(IInitializable.Initialize));

            // NOTE: タイトル画面の表示を行う
            var viewController = ViewFactory.Create<TitleViewController>(); 
            Canvas.RootViewController.Show(viewController, false);
        }
    }
}

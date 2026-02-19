using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using UIKit;
using UnityEngine.Assertions;
using WPFramework.Constants.Zenject;
using WPFramework.Debugs.Environment.Presentation.Views;
using WPFramework.Modules.Log;
using WPFramework.Presentation.Modules;
using Zenject;
using Object = UnityEngine.Object;

namespace WPFramework.Debugs.Environment
{
    public sealed class DebugEnvironmentSelector : IDisposable
    {
        [Inject] Context Context { get; }
        [Inject] IViewFactory ViewFactory { get; }
        Object InstantiateObject { get; set; }

        public async UniTask Invoke(CancellationToken cancellationToken)
        {
            // NOTE: 既に生成されていた場合特に何もしない
            if (InstantiateObject)
            {
                return;
            }

            try
            {
                ApplicationLog.Log(nameof(DebugEnvironmentSelector), "Debug Command Activated.");

                var canvas = Context.Container.ResolveId<UICanvas>(FrameworkInjectId.Canvas.System);
                var instantiateObject = Context.Container.Resolve<DebugEnvironmentSelectView>();
                Assert.IsNotNull(instantiateObject);

                var completionSource = new UniTaskCompletionSource<bool>();
                var controller = new DebugEnvironmentSelectViewController()
                {
                    TempleteView = instantiateObject
                };
                var controllerArgument = new DebugEnvironmentSelectViewController.Argument(
                    () => completionSource.TrySetCanceled(),
                    () => completionSource.TrySetResult(false));

                Context.Container.Bind<DebugEnvironmentSelectViewController>()
                    .FromInstance(controller)
                    .AsCached();
                Context.Container.Bind<DebugEnvironmentSelectViewController.Argument>()
                    .FromInstance(controllerArgument)
                    .AsCached();

                Context.Container.Inject(controller);
                canvas.RootViewController.PresentModally(controller);

                InstantiateObject = controller.View;

                // 何らかの原因でcancellationTokenが働いた場合はcompletionSourceをCancel扱いにする
                await using var _ =
                    cancellationToken.Register(
                        () => completionSource.TrySetCanceled(),
                        useSynchronizationContext: true);

                // ローカルデータ削除が行われた場合
                await completionSource.Task;
            }
            finally
            {
                // NOTE: 参照が残ってしまうとLeaked Managed Shellが発生するため参照を解放する
                InstantiateObject = null;
            }
        }

        public void Dispose()
        {
            InstantiateObject = null;
        }
    }
}

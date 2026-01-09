using System;
using System.Collections.Generic;
using GLOW.Debugs.AdminDebug.Presentation;
using GLOW.Debugs.Command.Presentations.Presenters;
using GLOW.Debugs.Command.Presentations.Views;
using UIKit;
using WonderPlanet.DebugActivator;
using WonderPlanet.DebugActivator.Invoker;
using WPFramework.Constants.Zenject;
using WPFramework.Modules.Log;
using Zenject;
using Object = UnityEngine.Object;

namespace GLOW.Debugs.Command.Presentations
{
    sealed class DebugCustomTabController : UITabController
    {
        Action OnUnloaded;

        public DebugCustomTabController()
        {
            PrefabName = "DebugTabView";
        }

        public void Init(List<UIViewController> controllers, Action onUnloaded)
        {
            OnUnloaded = onUnloaded;
            base.Init(controllers);
        }

        public override void UnloadView()
        {
            base.UnloadView();
            OnUnloaded?.Invoke();
        }
    }
    public sealed class DebugCommandActivator : IInitializable, IDisposable
    {
        [Inject] Context Context { get; }
        [Inject] IDebugCommandPresenter DebugCommandPresenter { get; }

        Object InstantiateObject { get; set; }

        public static event Action<IDebugCommandPresenter> OnDebugCommandActivated;
        public static event Action OnDebugCommandInactivated;

        void IInitializable.Initialize()
        {
            DebugActivator.Activated -= OnActivate;
            DebugActivator.Activated += OnActivate;

            DebugCommandPresenter.DidLoad = presenter =>
            {
                OnDebugCommandActivated?.Invoke(presenter);
            };

            DebugCommandPresenter.DidUnload = () =>
            {
                OnDebugCommandInactivated?.Invoke();
            };
        }

        void OnActivate(IDebugInvoker invoker)
        {
            // NOTE: 既に生成されていた場合特に何もしない
            if (InstantiateObject)
            {
                return;
            }

            ApplicationLog.Log(nameof(DebugCommandActivator), "Debug Command Activated.");

            var canvas = Context.Container.ResolveId<UICanvas>(FrameworkInjectId.Canvas.System);
            // NOTE: Client タブ
            var instantiateObject = Context.Container.Resolve<DebugCommandView>();
            UnityEngine.Assertions.Assert.IsNotNull(instantiateObject);
            var debugCommandController = new DebugCommandViewController
            {
                TempleteView = instantiateObject
            };
            Context.Container.Inject(debugCommandController);
            debugCommandController.LoadViewIfNeeded();
            debugCommandController.TabBarItem.Title = "ローカル";

            // NOTE: Server タブ（Admin Debug）
            var adminDebugController = new AdminDebugViewController()
            {
                TempleteView = Context.Container.Resolve<AdminDebugView>()
            };
            Context.Container.Inject(adminDebugController);
            adminDebugController.LoadViewIfNeeded();
            adminDebugController.TabBarItem.Title = "リモート";

            // NOTE: UITab で Client と Server を表示
            var tabController = new DebugCustomTabController();
            tabController.Init(new List<UIViewController>()
                {
                    debugCommandController,
                    adminDebugController
                },
                () =>
                {
                    // NOTE: 参照が残ってしまうとLeaked Managed Shellが発生するため参照を解放する
                    InstantiateObject = null;
                });

            canvas.RootViewController.PresentModally(tabController);
            InstantiateObject = tabController.View;
        }

        public static void Enable()
        {
            DebugActivator.Enable();
        }

        public static void Disable()
        {
            DebugActivator.Disable();
        }

        void IDisposable.Dispose()
        {
            DebugActivator.Activated -= OnActivate;
            DebugActivator.Disable();
            InstantiateObject = null;

            DebugCommandPresenter.DidLoad = null;
            DebugCommandPresenter.DidUnload = null;
        }
    }
}

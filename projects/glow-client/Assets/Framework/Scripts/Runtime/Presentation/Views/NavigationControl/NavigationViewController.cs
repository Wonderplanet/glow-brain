using System;
using System.Collections;
using System.Collections.Generic;
using System.Linq;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;
using Object = UnityEngine.Object;

namespace WPFramework.Presentation.Views
{
    public class NavigationViewController : NavigationViewController<NavigationView>
    {
    }

    public class NavigationViewController<T> : UIViewController<T>, IViewNavigation, IEscapeResponder where T : NavigationView
    {
        class ContextInfo
        {
            public INavigationViewContext Context { get; }
            public UIViewController RootViewController { get; }

            public ContextInfo(INavigationViewContext context, UIViewController viewController)
            {
                Context = context;
                RootViewController = viewController;
            }
        }

        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }
        [Inject] Context Context { get; }

        protected override bool ShouldAutomaticallyForwardAppearanceMethods => false;

        readonly List<UIViewController> _viewControllerList = new List<UIViewController>();
        readonly List<ContextInfo> _contextStack = new List<ContextInfo>();

        public IReadOnlyList<UIViewController> ViewControllerList => _viewControllerList;

        public INavigationViewContext CurrentContext =>
            _contextStack.Count == 0 ? null : _contextStack.Last().Context;

        public UIViewController TopViewController =>
            _viewControllerList.Count == 0 ? null : _viewControllerList[_viewControllerList.Count - 1];

        public bool IsEnableEscape { get; set; } = true;

        bool _isInitializeEscape = false;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ActualView.NavigationBar.BackButtonOnClickEvent.AddListenerAsExclusive(OnBack);
        }

        protected IEnumerator AppearanceTransition(UIViewController vc, bool isAppearing, bool animated)
        {
            vc.BeginAppearanceTransition(true, animated);

            if (animated)
            {
                if (TopViewController.View.gameObject.activeInHierarchy)
                {
                    var schema = TopViewController.View.GetTransitionSchema();
                    yield return schema.AppearanceTransition(isAppearing);
                }
            }

            vc.EndAppearanceTransition();
        }

        public override void ViewWillAppear(bool animated)
        {
            BindEscapeResponder();

            base.ViewWillAppear(animated);

            View.StartCoroutine(AppearanceTransition(TopViewController, true, animated));
        }

        void BindEscapeResponder()
        {
            if (_isInitializeEscape)
            {
                return;
            }

            EscapeResponderRegistry.Bind(this, View);

            _isInitializeEscape = true;
        }

        public override void ViewWillDisappear(bool animated)
        {
            base.ViewWillDisappear(animated);
            TopViewController.BeginAppearanceTransition(false, animated);
        }

        public override void ViewDidDisappear()
        {
            base.ViewDidDisappear();

            TopViewController.EndAppearanceTransition();
        }

        // NOTE: SetViewControllerは現在冪等生がない
        //       また、親のControllerがTransitionする前にコールする必要がある
        public void SetViewController(UIViewController rootViewController, INavigationViewContext context)
        {
            SetViewControllers(new List<UIViewController>() { rootViewController }, context);
        }

        public void SetViewControllers(IReadOnlyList<UIViewController> controllers, INavigationViewContext context)
        {
            SetViewControllers(
                new List<NavigationViewContextGroup>()
                {
                    new NavigationViewContextGroup(context, controllers)
                }
            );
        }

        public void SetViewControllers(List<NavigationViewContextGroup> contextGroups)
        {
            for (var i = 0; i < contextGroups.Count; i++)
            {
                var group = contextGroups[i];
                var context = group.Context;
                var isContextDisable = false;
                if (context != default)
                {
                    AddContext(new ContextInfo(group.Context, group.ControllerList.First()));
                    context.NavigationBackgroundItem.transform.SetParent(ActualView.BgContent, false);
                    isContextDisable = i != contextGroups.Count - 1;
                    context.NavigationBackgroundItem.Hidden = isContextDisable;
                }

                for (var j = 0; j < group.ControllerList.Count; j++)
                {
                    var controller = group.ControllerList[j];
                    AddController(controller);
                    controller.View.transform.SetParent(ActualView.Content, false);
                    controller.View.Hidden =
                        isContextDisable || (j != group.ControllerList.Count - 1); // 最後のView以外は配置後非表示にする
                }
            }

            if (CurrentContext != default) ChangeContextTitle(CurrentContext.ContextTitle);
            ChangeTitle(TopViewController.NavigationItem.Title, false);
            ObserveTitle(TopViewController);
            SetNavigationBarHidden(_viewControllerList.Count <= 1, false);
        }

        void AddContext(ContextInfo contextInfo)
        {
            Context.Container.Inject(contextInfo.Context);
            _contextStack.Add(contextInfo);
        }

        void RemoveContext(ContextInfo contextInfo)
        {
            _contextStack.Remove(contextInfo);
        }

        void AddController(UIViewController controller)
        {
            AddChild(controller);
            _viewControllerList.Add(controller);
        }

        void RemoveController(UIViewController controller)
        {
            RemoveChild(controller);
            _viewControllerList.Remove(controller);
        }

        public void SetNavigationBarHidden(bool hidden, bool animated)
        {
            if (ActualView.NavigationBar.Hidden == hidden) return;
            if (animated)
            {
                if (hidden)
                {
                    ActualView.NavigationBar.Animate("disappear", () => ActualView.NavigationBar.Hidden = true);
                }
                else
                {
                    ActualView.NavigationBar.Hidden = false;
                    ActualView.NavigationBar.Animate("appear");
                }
            }
            else
            {
                ActualView.NavigationBar.Hidden = hidden;
            }
        }

        void OnBack()
        {
            Pop();
        }

        protected override void PresentChildController(UIViewController controller, bool animated = true, Action completion = null)
        {
            Push(controller, animated, completion);
        }

        protected override void DismissChildController(UIViewController controller, bool animated = true, Action completion = null)
        {
            if (TopViewController != controller) return;
            Pop(animated, completion);
        }

        public virtual void Push(UIViewController controller, bool animated = true, Action completion = null)
        {
            Push(controller, null, animated, completion);
        }

        public void Push(UIViewController controller, INavigationViewContext context, bool animated = true, Action completion = null)
        {
            Push(new List<UIViewController>() { controller }, context, animated, completion);
        }

        public void Push(List<UIViewController> controllers, INavigationViewContext context, bool animated = true, Action completion = null)
        {
            View.StartCoroutine(PushTask(controllers, context, animated, () =>
            {
                completion?.Invoke();
            }));
        }

        public virtual void Pop(bool animated = true, Action completion = null)
        {
            if (_viewControllerList.Count <= 1)
            {
                return;
            }

            View.StartCoroutine(PopTask(animated, () =>
            {
                completion?.Invoke();
            }));
        }

        public void PopRoot(bool animated = true, Action completion = null)
        {
            if (2 >= _viewControllerList.Count)
            {
                Pop();
                return;
            }

            View.StartCoroutine(PopRootTask(animated, completion));
        }

        public void PopToRootAndPush(UIViewController controller, bool animated = true, Action completion = null)
        {
            PopToRootAndPush(controller, null, animated, completion);
        }

        public void PopToRootAndPush(UIViewController controller, INavigationViewContext context, bool animated = true,
            Action completion = null)
        {
            PopToRootAndPush(new List<UIViewController>() { controller }, context, animated, completion);
        }

        public void PopToRootAndPush(List<UIViewController> controllers, INavigationViewContext context, bool animated = true, Action completion = null)
        {
            if (_viewControllerList.Count <= 1)
            {
                Push(controllers, context, animated, completion);
                return;
            }

            View.StartCoroutine(PopToRootAndPushTask(controllers, context, animated, () =>
            {
                completion?.Invoke();
            }));
        }

        IEnumerator PushTask(List<UIViewController> controllers, INavigationViewContext context, bool animated, Action completion)
        {
            var sourceVc = TopViewController;
            NavigationBackgroundItem sourceBg = null;
            NavigationBackgroundItem destBg = null;

            if (context != null)
            {
                if (_contextStack.Count != 0)
                {
                    sourceBg = _contextStack.Last().Context.NavigationBackgroundItem;
                }

                AddContext(new ContextInfo(context, controllers.First()));
                destBg = context.NavigationBackgroundItem;
            }

            var destVc = controllers.Last();
            for (var j = 0; j < controllers.Count - 1; j++)
            {
                var controller = controllers[j];
                AddController(controller);
                controller.View.transform.SetParent(ActualView.Content, false);
                controller.View.Hidden = true;
            }

            AddController(destVc);

            yield return Transition(sourceVc, destVc, sourceBg, destBg, animated,
                () =>
                {
                    if (sourceVc != null) sourceVc.View.Hidden = true;
                    if (CurrentContext != default) ChangeContextTitle(CurrentContext.ContextTitle);
                    ChangeTitle(destVc.NavigationItem.Title, animated);
                    ObserveTitle(destVc);
                    SetNavigationBarHidden(_viewControllerList.Count <= 1, animated);
                },
                () => { },
                () =>
                {
                    if (sourceBg != null) sourceBg.Hidden = true;
                });

            completion?.Invoke();
        }

        IEnumerator PopTask(bool animated, Action completion)
        {
            NavigationBackgroundItem sourceBg = null;
            NavigationBackgroundItem destBg = null;

            var sourceVc = TopViewController;
            var destVc = _viewControllerList[_viewControllerList.Count - 2];

            var contextInfo = _contextStack.LastOrDefault();
            if (contextInfo != default && contextInfo.RootViewController == sourceVc)
            {
                sourceBg = contextInfo.Context.NavigationBackgroundItem;
                RemoveContext(contextInfo);

                contextInfo = _contextStack.Last();
                destBg = contextInfo.Context.NavigationBackgroundItem;
            }

            RemoveController(sourceVc);

            SetNavigationBarHidden(_viewControllerList.Count <= 1, animated);
            yield return Transition(sourceVc, destVc, sourceBg, destBg, animated,
                () =>
                {
                    if (sourceVc != null) sourceVc.UnloadView();
                    if (CurrentContext != default) ChangeContextTitle(CurrentContext.ContextTitle);
                    ChangeTitle(destVc.NavigationItem.Title, animated);
                    ObserveTitle(destVc);
                },
                () => { },
                () =>
                {
                    if (sourceBg != null)
                    {
                        Object.Destroy(sourceBg.gameObject);
                    }
                });

            completion?.Invoke();
        }

        IEnumerator PopRootTask(bool animated, Action completion)
        {
            var sourceVc = _viewControllerList[_viewControllerList.Count - 1];
            var destVc = _viewControllerList[0];
            var sourceBg = _contextStack[_contextStack.Count - 1].Context.NavigationBackgroundItem;
            var destBg = _contextStack[0].Context.NavigationBackgroundItem;

            for (var i = _viewControllerList.Count - 1; i > 0; --i)
            {
                var vc = _viewControllerList[i];
                RemoveController(vc);
                if (sourceVc != null && vc != sourceVc)
                {
                    vc.UnloadView();
                }
            }

            for (var i = _contextStack.Count - 1; i > 0; --i)
            {
                var ct = _contextStack[i];
                RemoveContext(ct);

                if (sourceBg != null && ct.Context.NavigationBackgroundItem != sourceBg)
                {
                    Object.Destroy(ct.Context.NavigationBackgroundItem);
                }
            }

            SetNavigationBarHidden(_viewControllerList.Count <= 1, animated);
            yield return Transition(sourceVc, destVc, destBg, destBg, animated,
                () =>
                {
                    sourceVc?.UnloadView();

                    ChangeContextTitle(CurrentContext.ContextTitle);
                    ChangeTitle(destVc.NavigationItem.Title, animated);
                    ObserveTitle(destVc);
                },
                () => { },
                () =>
                {
                    if (sourceBg != null)
                    {
                        Object.Destroy(sourceBg.gameObject);
                    }
                }
            );

            completion?.Invoke();
        }

        IEnumerator PopToRootAndPushTask(IReadOnlyList<UIViewController> controllers, INavigationViewContext context, bool animated, Action completion)
        {
            UIViewController sourceVc = null;
            UIViewController destVc = null;
            NavigationBackgroundItem sourceBg = null;
            NavigationBackgroundItem destBg = null;

            for (var i = _viewControllerList.Count - 1; i > 0; i--)
            {
                var viewController = _viewControllerList[i];

                var contextInfo = _contextStack.Last();
                if (contextInfo.RootViewController == viewController)
                {
                    RemoveContext(contextInfo);

                    if (sourceBg == null)
                    {
                        sourceBg = contextInfo.Context.NavigationBackgroundItem;
                    }
                    else
                    {
                        if (contextInfo.Context.NavigationBackgroundItem != null)
                        {
                            Object.Destroy(contextInfo.Context.NavigationBackgroundItem.gameObject);
                        }
                    }
                }

                RemoveController(viewController);

                if (sourceVc != null)
                {
                    viewController.UnloadView();
                }
                else
                {
                    sourceVc = viewController;
                }
            }

            if (sourceBg != null)
            {
                destBg = CurrentContext.NavigationBackgroundItem;
            }

            destVc = controllers.Last();

            if (context != null)
            {
                AddContext(new ContextInfo(context, controllers.First()));
                destBg = context.NavigationBackgroundItem;
            }

            for (var j = 0; j < controllers.Count - 1; j++)
            {
                var controller = controllers[j];
                AddController(controller);
                controller.View.transform.SetParent(ActualView.Content, false);
                controller.View.Hidden = true;
            }

            AddController(destVc);

            SetNavigationBarHidden(_viewControllerList.Count <= 1, animated);
            yield return Transition(sourceVc, destVc, destBg, destBg, animated,
                () =>
                {
                    sourceVc?.UnloadView();

                    ChangeContextTitle(CurrentContext.ContextTitle);
                    ChangeTitle(destVc.NavigationItem.Title, animated);
                    ObserveTitle(destVc);
                },
                () => { },
                () =>
                {
                    if (sourceBg != null)
                    {
                        Object.Destroy(sourceBg.gameObject);
                    }
                }
            );

            completion?.Invoke();
        }

        IEnumerator Transition(
            UIViewController sourceVc,
            UIViewController destVc,
            NavigationBackgroundItem sourceBg,
            NavigationBackgroundItem destBg,
            bool animated,
            Action onSourceTransitionComplete,
            Action onDestTransitionComplete,
            Action onBgTransitionComplete)
        {
            if (animated)
            {
                View.UserInteraction = false;
            }

            ActualView.SourceContainer.Hidden = false;
            ActualView.BgSourceContainer.Hidden = false;

            if (sourceVc != null)
            {
                sourceVc.View.transform.SetParent(ActualView.SourceContainer.transform, false);
                sourceVc.View.Hidden = false;
            }

            if (destVc != null)
            {
                destVc.View.transform.SetParent(ActualView.DestinationContainer.transform, false);
                destVc.View.Hidden = false;
            }

            if (sourceBg != null)
            {
                sourceBg.transform.SetParent(ActualView.BgSourceContainer.transform, false);
                sourceBg.Hidden = false;
            }

            if (destBg != null)
            {
                destBg.transform.SetParent(ActualView.BgDestinationContainer.transform, false);
                destBg.Hidden = false;
            }

            ActualView.BgSourceContainer.Hidden = false;
            ActualView.BgDestinationContainer.Hidden = true;

            if (sourceVc != null)
            {
                ActualView.SourceContainer.Hidden = false;
                sourceVc.BeginAppearanceTransition(false, animated);
                if (animated)
                {
                    if (sourceVc.View.gameObject.activeInHierarchy)
                    {
                        yield return sourceVc.View.GetTransitionSchema().AppearanceTransition(false);
                    }
                }

                sourceVc.EndAppearanceTransition();
                sourceVc.View.transform.SetParent(ActualView.Content, false);
                sourceVc.View.Hidden = true;
                onSourceTransitionComplete?.Invoke();
            }

            ActualView.BgDestinationContainer.Hidden = false;
            Coroutine backgroundTask = null;
            if (destBg != null)
            {
                backgroundTask = ActualView.BgDestinationContainer.GetTransitionSchema().AppearanceTransition(true);
            }

            if (destVc != null)
            {
                ActualView.DestinationContainer.Hidden = false;
                destVc.BeginAppearanceTransition(true, animated);
                if (animated)
                {
                    // yield return destVC.View.GetTransitionSchema().AppearanceTransition(true);
                    if (destVc.View.gameObject.activeInHierarchy)
                    {
                        destVc.View.GetTransitionSchema().AppearanceTransition(true);
                    }
                }

                destVc.EndAppearanceTransition();
                destVc.View.transform.SetParent(ActualView.Content, false);
            }

            onDestTransitionComplete?.Invoke(); // 利用されていない

            if (backgroundTask != null)
            {
                yield return backgroundTask;
            }

            if (sourceBg != null)
            {
                sourceBg.transform.SetParent(ActualView.BgContent, false);
            }

            if (destBg != null)
            {
                destBg.transform.SetParent(ActualView.BgContent, false);
                onBgTransitionComplete?.Invoke();
            }

            if (animated)
            {
                View.UserInteraction = true;
            }

            ActualView.SourceContainer.Hidden = true;
            ActualView.DestinationContainer.Hidden = true;
            ActualView.BgSourceContainer.Hidden = true;
            ActualView.BgDestinationContainer.Hidden = true;
        }

        bool IEscapeResponder.OnEscape()
        {
            if (!IsEnableEscape)
            {
                return false;
            }

            return TryPopAndExecute();
        }

        protected bool TryPopAndExecute()
        {
            if (_viewControllerList.Count <= 1)
            {
                return false;
            }

            SystemSoundEffectProvider.PlaySeTap();
            Pop();
            return true;
        }

        void ObserveTitle(UIViewController controller)
        {
            if (TopViewController != null)
            {
                TopViewController.NavigationItem.OnTitleChange -= OnChangeTitle;
                if (controller.NavigationItem.Placeholder != null)
                {
                    controller.NavigationItem.Placeholder.Hidden = false;
                }
            }

            controller.NavigationItem.OnTitleChange += OnChangeTitle;

            if (controller.NavigationItem.Placeholder != null)
            {
                controller.NavigationItem.Placeholder.Hidden = true;
            }
        }

        void OnChangeTitle(string title)
        {
            ChangeTitle(title);
        }

        void ChangeContextTitle(string contextTitle)
        {
            if (!string.IsNullOrEmpty(contextTitle))
            {
                ActualView.NavigationBar.ContextTitle = contextTitle;
            }
        }

        void ChangeTitle(string title, bool animated = true)
        {
            if (animated)
            {
                ActualView.NavigationBar.SetTitleWithAnimate(title);
            }
            else
            {
                ActualView.NavigationBar.Title = title;
            }
        }
    }
}

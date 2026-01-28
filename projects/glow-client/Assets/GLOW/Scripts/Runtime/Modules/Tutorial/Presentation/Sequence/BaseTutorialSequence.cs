using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Modules.InvertMaskView.Domain.ValueObject;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Modules.Tutorial.Domain.ValueObject;
using GLOW.Modules.Tutorial.Presentation.Views;
using GLOW.Modules.TutorialMessageBox.Presentation.ViewModel;
using GLOW.Modules.TutorialTapIcon.Presentation.ValueObject;
using GLOW.Modules.TutorialTapIcon.Presentation.ViewModel;
using GLOW.Modules.TutorialTipDialog.Domain.Models;
using GLOW.Modules.TutorialTipDialog.Domain.UseCase;
using GLOW.Modules.TutorialTipDialog.Domain.ValueObject;
using GLOW.Modules.TutorialTipDialog.Presentation.View;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using UIKit;
using UniRx;
using UnityEngine;
using UnityEngine.SceneManagement;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;
using WPFramework.Presentation.Views;
using Zenject;
using Object = UnityEngine.Object;

namespace GLOW.Modules.Tutorial.Presentation.Sequence
{
    public abstract class BaseTutorialSequence : ITutorialSequence, IDisposable
    {
        [Inject] ITutorialHomeViewDelegate _tutorialHomeViewDelegate;
        [Inject] UICanvas _sceneUiCanvas;
        [Inject] IUIModalPresentationObserver _modalPresentationObserver;
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IHomeViewControl HomeViewControl { get; }
        [Inject] ProgressTutorialStatusUseCase ProgressTutorialStatusUseCase { get; }
        [Inject] TutorialTipDialogViewWireFrame TutorialTipDialogViewWireFrame { get; }
        [Inject] TutorialTipDialogUseCase TutorialTipDialogUseCase { get; }
        [Inject] CompleteFreePartTutorialUseCase CompleteFreePartTutorialUseCase { get; }

        UIViewController _currentViewController;
        List<UIHighlight> _highlightList = new List<UIHighlight>();
        UIViewController _grayOutController;
        TutorialViewController _tutorialViewController;
        TutorialCanvasController _tutorialCanvasController;
        CancellationTokenSource _cancellationTokenSource = new CancellationTokenSource();

        protected TutorialViewController TutorialViewController
        {
            get
            {
                if (_tutorialViewController == null)
                {
                    _tutorialViewController = new TutorialViewController();
                    TutorialCanvasController.Present(_tutorialViewController);
                }

                return _tutorialViewController;
            }
        }

        TutorialCanvasController TutorialCanvasController　
        {
            get
            {
                if (_tutorialCanvasController == null)
                {
                    _tutorialCanvasController = new TutorialCanvasController();
                    SceneManager.MoveGameObjectToScene(
                        _tutorialCanvasController.ActualView.gameObject,
                        _sceneUiCanvas.gameObject.scene);
                }

                return _tutorialCanvasController;
            }
        }

        public abstract UniTask Play(CancellationToken cancellationToken);

        // テキストメッセージ表示
        protected async UniTask ShowTutorialText(CancellationToken cancellationToken, string text, float positionY)
        {
            var tcs = new UniTaskCompletionSource();
            TutorialViewController.ShowMessageBox(
                new TutorialMessageBoxViewModel(
                    new TutorialMessageBoxText(text), 
                    new TutorialMessageBoxPositionY(positionY)),
                AllowTapOnlyInvertMaskedAreaFlag.False,
                () =>  tcs.TrySetResult()
            );
            await tcs.Task.AttachExternalCancellation(cancellationToken);
        }

        protected async UniTask HideTutorialText(CancellationToken cancellationToken)
        {
            var tcs = new UniTaskCompletionSource();
            TutorialViewController.HideMessageBox(() =>  tcs.TrySetResult());

            // チュートリアルテキストの非表示アニメーションの待機
            await UniTask.Delay(300, cancellationToken: cancellationToken);
            await tcs.Task.AttachExternalCancellation(cancellationToken);
        }
        
        protected void HighlightTarget(
            string highlightTargetName,
            Func<TutorialIndicatorTarget, bool> search = null)
        {
            var target = FindTargetObject(highlightTargetName, search);
            
            if (target == null) return;

            // 後からハイライトしたもののsortOrderを高く表示する
            var highlight = UIHighlight.Highlight(target.gameObject, _highlightList.Count);
            _highlightList.Add(highlight);
        }

        protected void UnHighlightTarget()
        {
            // 逆順で処理することでインデックスのずれを防止
            for (int i = _highlightList.Count - 1; i >= 0; i--)
            {
                var highlight = _highlightList[i];
                if (highlight == null) continue;
        
                _highlightList.RemoveAt(i);
                highlight.UnHighlight();
                Object.Destroy(highlight);
            }

            _highlightList.Clear();
        }

        // 指指し表示
        protected void ShowIndicator(
            string indicatorAnchorTargetName, 
            Func<TutorialIndicatorTarget, bool> search = null)
        {
            var target = FindTargetObject(indicatorAnchorTargetName, search);
            if (target == null) return;

            // rectにcanvasを設定するクラスを用意
            HighlightTarget(indicatorAnchorTargetName, search);

            var rectTransform = target.GetRectTransform();
            var targetCanvas = rectTransform.root.GetComponentInParent<Canvas>();

            if(targetCanvas == null)
            {
                targetCanvas = rectTransform.GetComponentInParent<Canvas>();
            }

            if (targetCanvas == null) return;

            Camera camera = null;
            // renderModeがScreenSpaceOverlayの場合はカメラ不要なためnullで渡す
            if(targetCanvas.renderMode != RenderMode.ScreenSpaceOverlay)
            {
                camera = Camera.main;
            }

            // 対象オブジェクトの中心を取得する
            var localOffset = rectTransform.rect.center;
            var worldPoint = rectTransform.TransformPoint(localOffset);
            var screenPoint = RectTransformUtility.WorldToScreenPoint(camera, worldPoint);
            var position = TutorialViewController.CalculateInvertMaskPosition(screenPoint);
            var rectSize = rectTransform.sizeDelta;
            TutorialViewController.ShowTapIcon(
                new TutorialTapIconViewModel(
                    new TutorialTapIconPosition(position.X, position.Y + rectSize.y / 2 + 80),
                    new TutorialTapEffectPosition(position.X, position.Y),
                    ReverseFlag.True
                )
            );
        }
        
        protected void ShowArrowIndicator(
            string indicatorAnchorTargetName, 
            ReverseFlag reverseFlag, 
            Func<TutorialIndicatorTarget, bool> search = null)
        {
            var target = FindTargetObject(indicatorAnchorTargetName, search);
            if (target == null) return;

            // ハイライト
            HighlightTarget(indicatorAnchorTargetName, search);
            
            // rectにcanvasを設定するクラスを用意
            var rectTransform = target.GetRectTransform();
            var targetCanvas = rectTransform.root.GetComponentInParent<Canvas>();

            if (targetCanvas == null)
            {
                targetCanvas = rectTransform.GetComponentInParent<Canvas>();
            }

            if (targetCanvas == null) return;

            Camera camera = null;

            // renderModeがScreenSpaceOverlayの場合はカメラ不要なためnullで渡す
            if (targetCanvas.renderMode != RenderMode.ScreenSpaceOverlay)
            {
                camera = Camera.main;
            }
            
            // 対象オブジェクトの中心を取得する
            var localOffset = rectTransform.rect.center;
            var worldPoint = rectTransform.TransformPoint(localOffset);
            var screenPoint = RectTransformUtility.WorldToScreenPoint(camera, worldPoint);
            var position = TutorialViewController.CalculateInvertMaskPosition(screenPoint);
            var offsetY = rectTransform.sizeDelta.y / 2 + 50;
            if (reverseFlag) offsetY = -offsetY;
            
            var iconPosition = new TutorialTapIconPosition(position.X, position.Y + offsetY);
            
            TutorialViewController.ShowArrowIcon(iconPosition, reverseFlag);
        }
        
        protected void HideArrowIndicator()
        {
            Debug.Log("Tutorial HideArrowIndicator start : HideArrowIndicator");
            TutorialViewController.HideArrowIcon();

            UnHighlightTarget();
        }

        protected async UniTask WaitClickEvent(
            CancellationToken cancellationToken,
            string indicatorAnchorTargetName,
            Func<TutorialIndicatorTarget, bool> search = null)
        {
            var target = FindTargetObject(indicatorAnchorTargetName, search);
            if (target == null) return;

            var button = target.GetComponent<Button>();
            if (button == null) return;

            var tcs = new UniTaskCompletionSource();
            var eventListener = new ButtonClickEventListener(() => tcs.TrySetResult());
            button.onClick.AddListener(eventListener.OnClick);
            try
            {
                await tcs.Task.AttachExternalCancellation(cancellationToken);
            }
            finally
            {
                button.onClick.RemoveListener(eventListener.OnClick);
            }
        }

        protected void HideIndicator()
        {
            TutorialViewController.HideTapIcon();
            UnHighlightTarget();
        }

        protected TutorialIndicatorTarget FindTargetObject(
            string targetName,
            Func<TutorialIndicatorTarget, bool> search = null)
        {
            var targets = new List<TutorialIndicatorTarget>();
            var canvases = UICanvas.Canvases;
            foreach (var canvas in canvases)
            {
                targets.AddRange(
                    canvas.RootViewController.View.GetComponentsInChildren<TutorialIndicatorTarget>());
            }

            if (targets.IsEmpty())
            {
                targets.AddRange(
                    Object.FindObjectsByType<TutorialIndicatorTarget>(
                        FindObjectsInactive.Exclude, 
                        FindObjectsSortMode.None));
            }

            var targetCandidates = targets
                .Where(t => t.TargetName == targetName);

            if (search != null)
            {
                targetCandidates = targetCandidates.Where(search);
            }

            return targetCandidates.FirstOrDefault();
        }

        protected async UniTask DelayWithInteractionDisable<T>(
            float time, 
            CancellationToken cancellationToken) where T : UIView
        {
            var targets = new List<UIView>();
            var canvases = UICanvas.Canvases;
            foreach (var canvas in canvases)
            {
                targets.AddRange(canvas.RootViewController.View.GetComponentsInChildren<T>());
            }

            var target = targets.FirstOrDefault();

            if (target == null)
            {
                return;
            }

            target!.UserInteraction = false;

            await UniTask.Delay(TimeSpan.FromSeconds(time), cancellationToken: cancellationToken);
            target!.UserInteraction = true;
        }

        protected async UniTask Delay(float time, CancellationToken cancellationToken)
        {
            await UniTask.Delay(TimeSpan.FromSeconds(time), cancellationToken: cancellationToken);
        }

        // ViewとModalの表示待ち
        protected async UniTask WaitViewPresentation<T>(CancellationToken cancellationToken)  where T : UIViewController
        {
            using var cts = new CancellationTokenSource();
            var tokenSource = CancellationTokenSource.CreateLinkedTokenSource(cancellationToken, cts.Token);

            // 表示済みモーダルは検知できないので注意が必要
            var modalObserveTask = _modalPresentationObserver.PresentationTransitionDidEndAsObservable()
                .Where(ev => ev.PresentingViewController is T)
                .Select(ev => ev.PresentingViewController)
                .ToUniTask(useFirstValue: true, cancellationToken: cancellationToken);

            var homeViewObserveTask = UniTask.Create(async () =>
            {
                await UniTask.WaitUntil(() => 
                    _tutorialHomeViewDelegate.IsPresented<T>(), 
                    cancellationToken: tokenSource.Token);
            });

            try
            {
                await UniTask.WhenAny(new[] { modalObserveTask, homeViewObserveTask });
            }
            finally
            {
                cts.Cancel();
            }
        }
        
        protected void EnableHomeHeaderTap()
        {
            _tutorialHomeViewDelegate.EnableHomeHeaderTap();
        }

        protected void DisableHomeHeaderTap()
        {
            _tutorialHomeViewDelegate.DisableHomeHeaderTap();
        }

        // モーダルが消えるのを待つ
        protected async UniTask<UIViewController> WaitDismissModal<T>(
            CancellationToken cancellationToken)  where T : UIViewController
        {
            var modalObserveTask = _modalPresentationObserver.DismissalTransitionDidEndAsObservable()
                .Where(ev => ev.PresentingViewController is T)
                .Select(ev => ev.PresentingViewController)
                .ToUniTask(useFirstValue: true, cancellationToken: cancellationToken);
            return await modalObserveTask;
        }

        protected void ShowOverlayGrayOut()
        {
            _grayOutController = new UIViewController() { PrefabName = "TutorialGrayOutView" };   // TODO:定数化
            _grayOutController.View.transform.SetParent(TutorialViewController.View.transform, false);
        }

        protected void HideOverlayGrayOut()
        {
            if(_grayOutController == null) return;
            
            _grayOutController.Dismiss();
            _grayOutController = null;
        }

        protected void HideGrayOut()
        {
            if(_grayOutController == null) return;
            
            _grayOutController.Dismiss();
            _grayOutController = null;
        }

        protected async UniTask FadeInGrayOut(CancellationToken cancellationToken)
        {
            var tcs = new UniTaskCompletionSource();

            _grayOutController = new UIViewController() { PrefabName = "TutorialGrayOutView" };   // TODO:定数化
            _grayOutController.View.transform.SetParent(_sceneUiCanvas.RootViewController.View.transform, false);
            _grayOutController.View.Animate("appear", () => tcs.TrySetResult());        // TODO:定数化

            await tcs.Task.AttachExternalCancellation(cancellationToken);
        }

        protected async UniTask FadeOutGrayOut(CancellationToken cancellationToken)
        {
            var tcs = new UniTaskCompletionSource();

            _grayOutController.View.Animate("disappear", () =>
            {
                _grayOutController.Dismiss();
                _grayOutController = null;
                tcs.TrySetResult();
            });

            await tcs.Task.AttachExternalCancellation(cancellationToken);
        }

        public void Dispose()
        {
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = null;

            if(_grayOutController != null)
            {
                _grayOutController.Dismiss();
                _grayOutController = null;
            }

            if (_tutorialCanvasController != null)
            {
                _tutorialCanvasController.UnloadView();
                _tutorialCanvasController = null;
            }
        }
        
        protected IReadOnlyList<TutorialTipModel> GetTutorialTips(MasterDataId tutorialId)
        {
            return TutorialTipDialogUseCase.GetTutorialTipDialogModel(tutorialId).TutorialTipModels;
        }

        protected void ShowTutorialDialog(TutorialTipDialogTitle title, TutorialTipAssetPath assetPath)
        {
            TutorialTipDialogViewWireFrame.ShowTutorialTipDialog(TutorialViewController, title, assetPath);
        }
        
        protected void ShowTutorialDialogWithNextButton(TutorialTipDialogTitle title, TutorialTipAssetPath assetPath)
        {
            TutorialTipDialogViewWireFrame.ShowTutorialTipDialogWithNextButton(
                TutorialViewController, 
                title, 
                assetPath);
        }

        protected async UniTask ProgressTutorialStatus(CancellationToken cancellationToken)
        {
            await ProgressTutorialStatusUseCase.ProgressTutorialStatus(cancellationToken);
        }

        protected async UniTask CompleteFreePartTutorial(
            CancellationToken cancellationToken, 
            TutorialFunctionName functionName)
        {
            await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(cancellationToken, functionName);
        }
    }
}

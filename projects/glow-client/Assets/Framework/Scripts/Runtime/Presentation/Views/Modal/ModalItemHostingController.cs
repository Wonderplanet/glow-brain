using System;
using System.Collections;
using System.Collections.Generic;
using System.Linq;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Extensions;
using Object = UnityEngine.Object;

namespace WPFramework.Presentation.Views
{
    public class ModalItemHostingController : UIViewController<UIModalItemHostingWindow>
    {
        const string AppearAnimationKey = "appear";
        const string DisappearAnimationKey = "disappear";

        public IUIModalPresentationTransitionDelegate TransitionDelegate { get; set; }
        bool _calledDismissalTransitionWillBegin;
        bool _calledDismissalTransitionDidEnd;

        // 背景画像のスタックを管理
        static List<Image> _bgImageList = new List<Image>();
        Image _bgImage;

        public ModalItemHostingController()
        {
            PrefabName = "FrameworkModalItemHostingWindow";
        }

        public void SetViewController(UIViewController itemViewController, bool animated = true, Action completion = null)
        {
            View.StopAllCoroutines();
            View.StartCoroutine(PresentationTask(itemViewController, animated, completion));
        }

        protected override void DismissChildController(UIViewController controller, bool animated = true, Action completion = null)
        {
            View.StopAllCoroutines();
            View.StartCoroutine(DismissTask(controller, animated, completion));
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            if (!_calledDismissalTransitionWillBegin)
            {
                TransitionDelegate?.DismissalTransitionWillBegin();
            }

            if (!_calledDismissalTransitionDidEnd)
            {
                TransitionDelegate?.DismissalTransitionDidEnd(false);
            }
        }

        // // TODO: アニメーションづけ進み次第けす
        // // アニメーションづけが終わってないところの暫定対応。目障りなので
        // UIView GetContainerView(IViewTransitionSchema schema)
        // {
        //     if (schema is ViewTransitionSchema)
        //     {
        //         var wrapContainerPrefab = UIViewBundle.Main.FindView<UIView>("QueenBlockableCrossDissolveContainerView");
        //         var contaienr = GameObject.Instantiate(wrapContainerPrefab);
        //         contaienr.gameObject.name = "WrapContainer";
        //         contaienr.Hidden = false;
        //         contaienr.transform.SetParent(View.transform, false);
        //         return contaienr;
        //     }
        //     else
        //     {
        //         return ActualView.CreateWrapContainer();
        //     }
        // }

        IEnumerator PresentationTask(UIViewController controller, bool animated, Action completion = null)
        {
            AddChild(controller);

            var container = ActualView.CreateWrapContainer();
            controller.View.transform.SetParent(container.transform, false);

            TransitionDelegate?.PresentationTransitionWillBegin();
            controller.BeginAppearanceTransition(true, animated);

            // NOTE: CanvasGroup がない場合は追加
            var canvasGroup = controller.View.gameObject.GetComponent<CanvasGroup>();
            if (canvasGroup == null)
            {
                canvasGroup = controller.View.gameObject.AddComponent<CanvasGroup>();
                canvasGroup.alpha = 1;
                canvasGroup.interactable = true;
                canvasGroup.blocksRaycasts = true;
            }

            // NOTE: Animator がない場合は DefaultAnimation を設定
            var animator = controller.View.gameObject.GetComponent<Animator>();
            if (animator == null)
            {
                animator = controller.View.gameObject.AddComponent<Animator>();
            }

            // NOTE: runtimeAnimatorControllerに値がセットされていなければ
            //       ModalContextのDefaultAnimatorをセット
            if (!animator.runtimeAnimatorController)
            {
                var modalContext = View.GetComponent<ModalContext>();
                var runtimeAnimatorController = modalContext.DefaultAnimator;
                animator.runtimeAnimatorController = runtimeAnimatorController;
            }


            // NOTE: Animator はタイムスケールを無視
            animator.updateMode = AnimatorUpdateMode.UnscaledTime;

            // 背景のアルファ値同期開始
            StartPresentationBackgroundSync(container, animated);

            if (animated)
            {
                var containerAnimComplete = false;
                container.Animate(AppearAnimationKey, () => containerAnimComplete = true);

                View.UserInteraction = false;

                yield return controller.View.GetTransitionSchema().AppearanceTransition(true);

                View.UserInteraction = true;

                yield return new WaitUntil(() => containerAnimComplete);
            }

            // 背景のアルファ値同期停止
            StopBackgroundSync();

            controller.EndAppearanceTransition();
            TransitionDelegate?.PresentationTransitionDidEnd(true);


            completion?.Invoke();
        }

        IEnumerator DismissTask(UIViewController controller, bool animated = true, Action completion = null)
        {
            var container = ActualView.FindWrapContainer(controller.View);

            TransitionDelegate?.DismissalTransitionWillBegin();
            controller.BeginAppearanceTransition(false, animated);

            _calledDismissalTransitionWillBegin = true;

            // 背景同期開始
            StartDismissalBackgroundSync(animated);

            if (animated)
            {
                // 背景同期都合で無効にしている場合がある為、Animatorを有効化
                var animator = container.GetComponent<Animator>();
                if (animator) animator.enabled = true;

                var containerAnimComplete = false;
                container.Animate(DisappearAnimationKey, () => containerAnimComplete = true);

                View.UserInteraction = false;

                yield return controller.View.GetTransitionSchema().AppearanceTransition(false);

                View.UserInteraction = true;

                yield return new WaitUntil(() => containerAnimComplete);
            }

            // 背景のアルファ値同期停止
            StopBackgroundSync();

            controller.EndAppearanceTransition();
            TransitionDelegate?.DismissalTransitionDidEnd(true);

            _calledDismissalTransitionDidEnd = true;

            RemoveChild(controller);

            controller.UnloadView();
            // NOTE: containerがnullの場合があるのでそれらは無視する
            //       containerはFindで探しに行くため既に破棄されている場合がある
            if (container != null)
            {
                Object.Destroy(container.gameObject);
            }

            BeginAppearanceTransition(false, animated);
            EndAppearanceTransition();

            UnloadView();
            completion?.Invoke();
        }

        void StartPresentationBackgroundSync(UIView container, bool animated)
        {
            // 背景画像を取得してスタックに追加
            if (container is ModalContainerView modalContainer)
            {
                _bgImage = modalContainer.BackgroundImage;
            }
            var lastBgImage = _bgImageList.LastOrDefault();
            if (_bgImage) _bgImageList.Add(_bgImage);

            // 背景画像があるか
            if (!_bgImage || !lastBgImage)
            {
                return;
            }

            // Animatorに合わせる側はAnimatorを無効化
            var lastAnimator = lastBgImage.GetComponent<Animator>();
            if (lastAnimator) lastAnimator.enabled = false;

            if (animated)
            {
                ActualView.StartBackgroundSync(_bgImage, lastBgImage, _bgImage.color.a); ;
            }
            else
            {
                // アニメーションしない場合は即時同期
                lastBgImage.color = Color.clear;
                lastBgImage.enabled = false;
            }
        }

        void StartDismissalBackgroundSync(bool animated)
        {

            // 背景画像をスタックから削除
            if (!_bgImage) return;
            if (_bgImageList.Contains(_bgImage))
            {
                _bgImageList.Remove(_bgImage);
            }


            // 背景画像があるか
            Image lastBgImage = _bgImageList.LastOrDefault();
            if (!lastBgImage)
            {
                return;
            }

            lastBgImage.enabled = true;
            if(animated)
            {
                ActualView.StartBackgroundSync(_bgImage, lastBgImage, _bgImage.color.a);
            }
            else
            {
                // アニメーションしない場合は即時同期
                lastBgImage.color = _bgImage.color;
            }

        }

        void StopBackgroundSync()
        {
            // 背景のアルファ値同期停止
            ActualView.StopBackgroundSync();
        }
    }
}

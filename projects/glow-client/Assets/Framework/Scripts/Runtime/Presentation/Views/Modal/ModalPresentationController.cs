using System;
using System.Collections.Generic;
using System.Linq;
using UIKit;
using UnityEngine;
using UnityEngine.SceneManagement;

namespace WPFramework.Presentation.Views
{
    public class ModalPresentationController : UIPresentationController
    {
        const int BlurScreenOrder = (int)OverlayCanvasSortingOrder.BlurScreen; // 本来はカメラのエフェクトとしたい
        const int BaseOrder = (int)OverlayCanvasSortingOrder.Modal;
        const int SystemBaseOrder = (int)OverlayCanvasSortingOrder.SystemCanvasModal;

        readonly ModalPresentationContext _context;
        ModalItemHostingController _itemHostingController;

        public ModalPresentationController(UIViewController presented, UIViewController presenting, ModalPresentationContext context) : base(presented, presenting)
        {
            _context = context;
        }

        public override void Present(bool animated, Action completion)
        {
            var currentContext = presentedViewController.View.GetComponentInParent<UICanvas>();
            _itemHostingController = new ModalItemHostingController
            {
                // モーダル呼び出し時にCustomの場合はApplicationDelegateのDefaultModalWindowの名前の指定を変えても、
                // ここの名前で強制的に指定される
                // PrefabName = "FrameworkModalItemHostingWindow",
                PrefabName = "GLOWFrameworkModalItemHostingWindow",
                TransitionDelegate = this
            };

            var presentedUICanvas = presentedViewController.View.GetComponentInParent<UICanvas>();

            // 出し先のCanvasコンテキストに応じてオーダーを計算する
            IEnumerable<UICanvas> modalCanvases = null;
            var order = 0;
            if (presentedUICanvas.Canvas.renderMode == RenderMode.ScreenSpaceOverlay) // SystemCanvas
            {
                order = SystemBaseOrder;
                modalCanvases = UICanvas.Canvases
                    .Where(c => c.RootViewController is ModalItemHostingController)
                    .Where(c => c.Canvas.sortingOrder >= SystemBaseOrder);
            }
            else // Scene Base Canvas
            {
                order = BaseOrder;
                modalCanvases = UICanvas.Canvases
                    .Where(c => c.RootViewController is ModalItemHostingController)
                    .Where(c => c.Canvas.sortingOrder < SystemBaseOrder);
            }

            var canvases = modalCanvases as UICanvas[] ?? modalCanvases.ToArray();
            if (canvases.Count() != 0)
            {
                order = Mathf.Max(order, canvases.Max(m => m.Canvas.sortingOrder));
            }

            var targetCanvas = _itemHostingController.ActualView.Canvas;
            targetCanvas.sortingOrder = order + 1;

            _itemHostingController.ActualView.RootViewController = _itemHostingController;
            SceneManager.MoveGameObjectToScene(_itemHostingController.ActualView.gameObject, currentContext.gameObject.scene);

            var contextIndex = currentContext.transform.GetSiblingIndex();
            presentedUICanvas.transform.SetSiblingIndex(contextIndex);
            _itemHostingController.SetViewController(presentingViewController, animated, completion);
        }

        public override void PresentationTransitionWillBegin()
        {
            if (_context.Counter == 0/* && _context.blurScreen == null*/)
            {
                // _context.blurScreen = UIViewBundle.Main.InstantiateView<BlurScreen>();
                var gameObject = _itemHostingController.View.gameObject;
                SceneManager.MoveGameObjectToScene(gameObject, gameObject.scene);

                // var index = _itemHostingController.View.gameObject.transform.GetSiblingIndex();
                // _context.blurScreen.transform.SetSiblingIndex(index - 1);
                // _context.blurScreen.Canvas.sortingOrder = BlurScreenOrder;
                // _context.blurScreen.Animate("appear");
            }
            _context.Counter++;
        }

        public override void DismissalTransitionWillBegin()
        {
            _context.Counter--;
            if (_context.Counter <= 0)
            {
                _context.Counter = 0;
            }

            /*
            Observable.TimerFrame(1)
                .Subscribe(_ =>
                {
                    if (_context.Counter == 0)
                    {
                        if (_context.blurScreen != null)
                        {
                            _context.blurScreen.Animate("disappear", () =>
                            {
                                GameObject.Destroy(_context.blurScreen.gameObject);
                                _context.blurScreen = null;
                            });
                        }
                    }
                }).AddTo(_context.blurScreen);
            */
        }
    }
}

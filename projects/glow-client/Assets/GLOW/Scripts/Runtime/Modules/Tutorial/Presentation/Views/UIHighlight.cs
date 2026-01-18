using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Views;

namespace GLOW.Modules.Tutorial.Presentation.Views
{
    /// <summary>
    /// UIをハイライトする
    /// </summary>
    public class UIHighlight : MonoBehaviour
    {
        Canvas _canvas;
        GraphicRaycaster _graphicRaycaster;

        public static UIHighlight Highlight(GameObject gameObject, int additionalSortingOrder = 0)
        {
            // 既にハイライトされている場合は何もしない
            var helper = gameObject.GetComponent<UIHighlight>();
            if (helper != null)
            {
                return helper;
            }

            helper = gameObject.AddComponent<UIHighlight>();
            helper.Highlight(additionalSortingOrder);
            return helper;
        }

        public void Highlight(int additionalSortingOrder = 0)
        {
            // このオブジェクトにCanvasがアタッチされていない場合はアタッチする
            _canvas = this.gameObject.GetComponent<Canvas>();
            if (_canvas == null)
            {
                _canvas = this.gameObject.AddComponent<Canvas>();
            }

            _graphicRaycaster = this.gameObject.GetComponent<GraphicRaycaster>();
            if (_graphicRaycaster == null)
            {
                _graphicRaycaster = this.gameObject.AddComponent<GraphicRaycaster>();
            }

            // 親のレンダーモードを引き継ぐ
            _canvas.renderMode = this.GetComponentInParent<Canvas>().renderMode;
            _canvas.overrideSorting = true;
            _canvas.sortingOrder = (int)OverlayCanvasSortingOrder.UIHighlight + additionalSortingOrder;

        }

        void OnDestroy()
        {
            UnHighlight();
        }

        public void UnHighlight()
        {
            if (_graphicRaycaster != null)
            {
                Destroy(_graphicRaycaster);
            }

            if (_canvas != null)
            {
                Destroy(_canvas);
            }
        }

    }
}

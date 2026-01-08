using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class FullScreenComponent : UIObject
    {
        protected override void Awake()
        {
            base.Awake();
            UpdateRectTransform();
        }

        protected override void Start()
        {
            base.Start();
            UpdateRectTransform();
        }

        void UpdateRectTransform()
        {
            var uiCamera = Camera.main;

            // 親オブジェクトにおける画面中央の位置を計算
            var parentRectTransform = transform.parent as RectTransform;
            if (parentRectTransform == null) return;

            var screenSize = new Vector2(Screen.width, Screen.height);
            var screenCenter = screenSize * 0.5f;

            RectTransformUtility.ScreenPointToLocalPointInRectangle(
                parentRectTransform,
                screenCenter,
                uiCamera,
                out Vector2 centerOnParent);

            var topRight = new Vector2(Screen.width, Screen.height);
            var bottomLeft = new Vector2(0, 0);

            RectTransformUtility.ScreenPointToLocalPointInRectangle(
                parentRectTransform,
                topRight,
                uiCamera,
                out Vector2 topRightOnParent);

            RectTransformUtility.ScreenPointToLocalPointInRectangle(
                parentRectTransform,
                bottomLeft,
                uiCamera,
                out Vector2 bottomLeftOnParent);

            // RectTransformが画面全体になるように設定
            var parentPivotPosFromCenter = parentRectTransform.rect.size * (parentRectTransform.pivot - new Vector2(0.5f, 0.5f));
            RectTransform.anchoredPosition = centerOnParent + parentPivotPosFromCenter;

            RectTransform.sizeDelta = topRightOnParent - bottomLeftOnParent;
            RectTransform.localScale = Vector3.one;
            RectTransform.localRotation = Quaternion.identity;
            RectTransform.pivot = new Vector2(0.5f, 0.5f);
            RectTransform.anchorMin = new Vector2(0.5f, 0.5f);
            RectTransform.anchorMax = new Vector2(0.5f, 0.5f);
        }
    }
}

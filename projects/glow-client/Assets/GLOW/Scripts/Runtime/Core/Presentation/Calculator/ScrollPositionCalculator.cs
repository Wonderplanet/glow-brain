using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Core.Presentation.Calculator
{
    /// <summary>
    /// スクロールビュー内で指定したRectTransformが、表示されるようにスクロール位置を計算するクラス
    /// </summary>
    public static class ScrollPositionCalculator
    {
        public static float CalculateTargetPositionInScroll(ScrollRect scrollRect, RectTransform targetRect, float topPadding = 0.0f)
        {
            var contentHeight = scrollRect.content.rect.height;
            var viewportHeight = scrollRect.viewport.rect.height;

            if (contentHeight < viewportHeight)
                return 0.0f;

            var rect = targetRect.rect;

            var targetPosY = targetRect.localPosition.y + rect.y + topPadding;
            var targetPos = contentHeight + targetPosY + rect.height;
            var normalizedPos = (targetPos - viewportHeight) / (contentHeight - viewportHeight);
            return Mathf.Clamp01(normalizedPos);
        }
    }
}

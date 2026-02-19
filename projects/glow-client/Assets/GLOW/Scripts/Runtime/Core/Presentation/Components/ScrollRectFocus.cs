using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Core.Presentation.Components
{
    [RequireComponent(typeof(ScrollRect))]
    public class ScrollRectFocus : MonoBehaviour
    {
        ScrollRect _scrollRect;
        void Start()
        {
            _scrollRect = GetComponent<ScrollRect>();
        }

        public void FocusContentCell(RectTransform cell)
        {
            Vector3 localPosition = cell.localPosition;
            float height = cell.rect.height;
            Vector3 topLocalPosition = localPosition + new Vector3(0, (1 - cell.pivot.y) * height, 0);

            _scrollRect.content.localPosition = new Vector3(0, -topLocalPosition.y, 0);
        }
    }
}

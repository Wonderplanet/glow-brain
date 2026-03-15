using UnityEngine.UI;
using WPFramework.Presentation.Components;

namespace WPFramework.Presentation.Extensions
{
    public static class ScrollRectExtension
    {
        public static ICollectionCellAnimation CreateTweenAnimation(this ScrollRect scrollRect)
        {
            return new ScrollRectCollectionCellAnimation(scrollRect);
        }
    }
}

using UnityEngine;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public interface IHomeTapBlock
    {
        void ShowTapBlock(bool shouldShowGrayScale,RectTransform invertMaskRect, float duration);
        void HideTapBlock(bool shouldShowGrayScale,float duration);
    }
}
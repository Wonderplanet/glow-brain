using UIKit;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Views.InGamePause
{
    public sealed class InGamePauseView : UIView
    {
        [SerializeField] CanvasGroup _canvasGroup;
        public CanvasGroup CanvasGroup => _canvasGroup;
    }
}

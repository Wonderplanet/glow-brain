using GLOW.Core.Presentation.Components;
using GLOW.Modules.TutorialTapIcon.Presentation.ViewModel;
using UnityEngine;

namespace GLOW.Modules.TutorialTapIcon.Presentation.Components
{
    public class TutorialTapIconComponent : UIObject
    {
        const string TapIconAnim = "Tap";
        [SerializeField] Animator _tapIconAnimator;

        public void Setup(TutorialTapIconViewModel viewModel)
        {
            RectTransform.anchoredPosition = new Vector2(viewModel.TutorialTapIconPosition.X, viewModel.TutorialTapIconPosition.Y);
            
            // 反転させる場合はYのスケールを-1にする
            var localScaleY = viewModel.ReverseFlag ? -1 : 1;
            RectTransform.localScale = new Vector3(1, localScaleY, 1);
        }

        public void Show()
        {
            IsVisible = true;
            _tapIconAnimator.Play(TapIconAnim, 0, 0);
        }

        public void Hide()
        {
            Hidden = true;
        }

    }
}

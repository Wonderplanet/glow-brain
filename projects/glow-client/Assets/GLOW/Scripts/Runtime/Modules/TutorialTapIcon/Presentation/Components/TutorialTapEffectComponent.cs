using GLOW.Core.Presentation.Components;
using GLOW.Modules.TutorialTapIcon.Presentation.ViewModel;
using UnityEngine;

namespace GLOW.Modules.TutorialTapIcon.Presentation.Components
{
    public class TutorialTapEffectComponent : UIObject
    {
        const string TapEffectAnim = "Tap-Ef";
        [SerializeField] Animator _tapEffectAnimator;

        public void Setup(TutorialTapIconViewModel viewModel)
        {
            RectTransform.anchoredPosition = new Vector2(viewModel.TutorialTapEffectPosition.X, viewModel.TutorialTapEffectPosition.Y);
        }

        public void Show()
        {
            IsVisible = true;
            _tapEffectAnimator.Play(TapEffectAnim, 0, 0);
        }

        public void Hide()
        {
            Hidden = true;
        }
    }
}

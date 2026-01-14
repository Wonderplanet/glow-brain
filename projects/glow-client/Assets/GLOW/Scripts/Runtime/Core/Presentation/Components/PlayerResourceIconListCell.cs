using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class PlayerResourceIconListCell :
        UICollectionViewCell,
        IPlayerResourceIconAnimationCell
    {
        [SerializeField] PlayerResourceIconComponent _iconComponent;
        [SerializeField] Animator _animator;
        [SerializeField] CanvasGroup _canvasGroup;
        [SerializeField] string _appearanceAnimationName = "appear";

        public void Setup(PlayerResourceIconViewModel viewModel)
        {
            _iconComponent.Setup(viewModel);
        }

        public void PlayAppearanceAnimation(float normalizedTime = 0.0f)
        {
            gameObject.SetActive(true);
            _canvasGroup.alpha = 1.0f;
            _animator.Play(_appearanceAnimationName, 0, normalizedTime);
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_005);
        }

        public void SetEnable(bool enable)
        {
            _canvasGroup.alpha = enable ? 1.0f : 0.0f;
        }
    }
}

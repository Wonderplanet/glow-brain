using UnityEngine;

namespace GLOW.Scenes.UnitEnhance.Presentation.Views.Components
{
    public class UnitEnhanceLevelUpAvatarAnimationComponent : MonoBehaviour
    {
        [SerializeField] Animator _levelUpBottomAnimation;
        [SerializeField] Animator _levelUpTopAnimation;

        readonly string _animationTrigger = "Play";

        void Start()
        {
            _levelUpBottomAnimation.gameObject.SetActive(false);
            _levelUpTopAnimation.gameObject.SetActive(false);
        }

        public void PlayAnimation()
        {
            _levelUpBottomAnimation.gameObject.SetActive(true);
            _levelUpTopAnimation.gameObject.SetActive(true);
            _levelUpBottomAnimation.SetTrigger(_animationTrigger);
            _levelUpTopAnimation.SetTrigger(_animationTrigger);
        }

        public void EndAnimation()
        {
            _levelUpBottomAnimation.gameObject.SetActive(false);
            _levelUpTopAnimation.gameObject.SetActive(false);
        }
    }
}

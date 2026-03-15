using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.OutpostEnhance.Presentation.Views.Component
{
    public class OutpostEnhanceGateButtonGrayOutComponent : UIObject
    {
        [SerializeField] Animator _gateButtonGrayOutAnimator;

        readonly string _fadeInTrigger = "FadeIn";
        readonly string _fadeOutTrigger = "FadeOut";

        public void FadeInGrayOut()
        {
            _gateButtonGrayOutAnimator.Play(_fadeInTrigger);
        }

        public void FadeOutGrayOut()
        {
            _gateButtonGrayOutAnimator.Play(_fadeOutTrigger);
        }
    }
}

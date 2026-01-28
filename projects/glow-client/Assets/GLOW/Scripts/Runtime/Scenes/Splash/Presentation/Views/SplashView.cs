using System;
using GLOW.Scenes.Splash.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.Splash.Presentation.Views
{
    public sealed class SplashView : UIView
    {
        [SerializeField] Animator _splashAnimator;
        [SerializeField] SplashTouchLayer _splashTouchLayer;

        public void SetOnTouchLayerTouched(Action onTapAction)
        {
            _splashTouchLayer.OnTouch = onTapAction;
        }
        
        public void PlayDisappearAttentionSplashAnimation()
        {
            _splashAnimator.Play("Splash-Cautionstatement-out", 0, 0);
        }
    }
}

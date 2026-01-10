using UIKit;
using UnityEngine;

namespace GLOW.Core.Presentation.Views.Interaction
{
    public class CommonLoadingView : UIView
    {
        [SerializeField] UIAnimation _uiAnimation;

        protected override void Awake()
        {
            base.Awake();
            _uiAnimation.gameObject.SetActive(false);
        }

        public void StartAnimation()
        {
            _uiAnimation.gameObject.SetActive(true);
            _uiAnimation.Play();
        }

        public void StopAnimation()
        {
            _uiAnimation.gameObject.SetActive(false);
            _uiAnimation.Stop();
        }
    }
}

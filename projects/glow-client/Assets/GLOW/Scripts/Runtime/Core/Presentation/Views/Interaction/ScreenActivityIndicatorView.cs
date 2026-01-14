using UIKit;
using UnityEngine;

namespace GLOW.Core.Presentation.Views.Interaction
{
    public sealed class ScreenActivityIndicatorView : UIView
    {
        [SerializeField] CommonLoadingView _loadingView;

        protected override void Start()
        {
            base.Start();
            _loadingView.StartAnimation();
        }
    }
}

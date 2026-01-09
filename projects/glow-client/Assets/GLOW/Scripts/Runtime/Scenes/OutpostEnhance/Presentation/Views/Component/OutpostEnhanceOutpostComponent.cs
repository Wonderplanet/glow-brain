using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Views.UIAnimator;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.OutpostEnhance.Presentation.Views.Component
{
    public class OutpostEnhanceOutpostComponent : UIObject
    {
        [SerializeField] Image _artwork;
        [SerializeField] UIAnimator _artworkAnimation;
        [SerializeField] CanvasGroup _canvasGroup;

        public void SetArtwork(ArtworkAssetPath path)
        {
            _canvasGroup.alpha = 1;
            UISpriteUtil.LoadSpriteWithFade(_artwork, path.Value, _artworkAnimation.PlayAnimation);
        }
    }
}

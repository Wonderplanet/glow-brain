using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ArtworkFragment.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.ArtworkFragment.Presentation.Components
{
    public class ArtworkFragmentComponent : MonoBehaviour
    {
        [SerializeField] UIText _positionNumText;
        // [SerializeField] UIObject _unlockAnimationObject;
        // [SerializeField] ArtworkFragmentReleaseAnimation _releaseAnimation;

        public void Setup(ArtworkFragmentViewModel model)
        {
            _positionNumText.SetText(model.Number.Value.ToString());
            if (model.IsUnlock)
            {
                gameObject.SetActive(false);
            }
        }

        public void SetCanvasGroupAlpha(float alpha)
        {
            GetComponent<CanvasGroup>().alpha = alpha;
        }

        //
        // public void PlayArtworkFragmentAnimation()
        // {
        //     _releaseAnimation.OnArtworkFragmentReleaseEventAction = () =>
        //     {
        //         GetComponent<CanvasGroup>().alpha = 0;
        //     };
        //     _unlockAnimationObject.Hidden = false;
        // }
        //
        // public void SkipArtworkFragmentAnimation()
        // {
        //     _unlockAnimationObject.Hidden = true;
        //     gameObject.SetActive(false);
        // }
    }
}

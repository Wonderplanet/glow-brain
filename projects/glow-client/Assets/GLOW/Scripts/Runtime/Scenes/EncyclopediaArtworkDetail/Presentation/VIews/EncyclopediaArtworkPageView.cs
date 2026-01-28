using GLOW.Scenes.ArtworkFragment.Presentation.Components;
using GLOW.Scenes.ArtworkFragment.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.ValueObjects;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views
{
    public class EncyclopediaArtworkPageView : UIView
    {
        [SerializeField] ArtworkFragmentPanelComponent _fragmentPanelComponent;
        [SerializeField] Button _artworkExpandButton;

        public void Setup(ArtworkFragmentPanelViewModel viewModel, ArtworkUnlockFlag unlock)
        {
            _fragmentPanelComponent.Setup(viewModel);
            _artworkExpandButton.interactable = unlock;
        }
    }
}

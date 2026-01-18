using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.ValueObjects;
using UIKit;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views
{
    public class EncyclopediaArtworkPageViewController :
        UIViewController<EncyclopediaArtworkPageView>
    {
        [Inject] IEncyclopediaArtworkPageViewDelegate ViewDelegate { get; }
        [Inject] Argument Args { get; }

        public record Argument(MasterDataId MstArtworkId);

        public Action<MasterDataId> OnSelectArtworkExpand { get; set; }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.OnViewWillAppear();
        }

        public void Setup(ArtworkFragmentPanelViewModel viewModel, ArtworkUnlockFlag unlock)
        {
            ActualView.Setup(viewModel, unlock);
        }

        [UIAction]
        void OnArtworkExpandButtonTapped()
        {
            OnSelectArtworkExpand?.Invoke(Args.MstArtworkId);
        }
    }
}

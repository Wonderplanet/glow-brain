using System;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;
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

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.OnViewWillAppear();
        }

        public void Setup(
            ArtworkFragmentPanelViewModel viewModel,
            ArtworkUnlockFlag unlock,
            ArtworkGradeMaxLimitFlag gradeMaxLimit)
        {
            ActualView.Setup(
                viewModel,
                unlock,
                gradeMaxLimit,
                ActualView.GetCancellationTokenOnDestroy());
        }

        public void InitializeViewTransform()
        {
            ActualView.InitializeViewTransform();
        }
    }
}

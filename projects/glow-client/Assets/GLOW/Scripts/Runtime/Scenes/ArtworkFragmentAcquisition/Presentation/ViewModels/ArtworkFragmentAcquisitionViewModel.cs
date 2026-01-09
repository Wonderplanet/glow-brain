using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.ViewModels
{
    public record ArtworkFragmentAcquisitionViewModel(
        ArtworkPanelViewModel ArtworkPanelViewModel,
        IReadOnlyList<ArtworkFragmentPositionNum> AcquiredArtworkFragmentIds,
        ArtworkName ArtworkName,
        ArtworkDescription Description,
        ArtworkCompleteFlag IsCompleted,
        HP AddHp)
    {
        public static ArtworkFragmentAcquisitionViewModel Empty { get; } = new(
            ArtworkPanelViewModel.Empty,
            new List<ArtworkFragmentPositionNum>(),
            ArtworkName.Empty,
            ArtworkDescription.Empty,
            ArtworkCompleteFlag.False,
            HP.Zero);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

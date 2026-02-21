using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Domain.Model;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;

namespace GLOW.Scenes.ArtworkFragmentAcquisition.Domain.Models
{
    public record ArtworkFragmentAcquisitionModel(
        ArtworkPanelModel ArtworkPanelModel,
        IReadOnlyList<ArtworkFragmentPositionNum> AcquiredArtworkFragmentPositions,
        ArtworkName ArtworkName,
        ArtworkDescription Description,
        ArtworkCompleteFlag IsCompleted,
        HP AddHp)
    {
        public static ArtworkFragmentAcquisitionModel Empty { get; } = new(
            ArtworkPanelModel.Empty,
            new List<ArtworkFragmentPositionNum>(),
            ArtworkName.Empty,
            ArtworkDescription.Empty,
            ArtworkCompleteFlag.False,
            new HP(0));

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

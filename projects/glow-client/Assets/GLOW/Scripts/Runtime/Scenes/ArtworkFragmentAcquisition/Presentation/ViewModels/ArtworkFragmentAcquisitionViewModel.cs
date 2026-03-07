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

        // 原画のかけらデータが設定されていないかつ、原画が完成している場合、全てのかけらがアンロックされているとみなす
        public bool IsCompletedWithoutFragmentData()
        {
            // 1. 現在の解放済みの原画のかけらが0である
            // 2. 今回解放する原画のかけら情報が0である
            // 3. CompleteFlagがTrueである
            return ArtworkPanelViewModel.FragmentPanelViewModel.IsAllLocked() &&
                   AcquiredArtworkFragmentIds.Count == 0 &&
                   IsCompleted == ArtworkCompleteFlag.True;
        }
    }
}

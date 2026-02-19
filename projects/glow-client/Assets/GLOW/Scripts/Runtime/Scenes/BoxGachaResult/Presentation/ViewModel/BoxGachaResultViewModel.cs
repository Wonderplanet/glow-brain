using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.ViewModels;
using GLOW.Scenes.GachaResult.Presentation;

namespace GLOW.Scenes.BoxGachaResult.Presentation.ViewModel
{
    public record BoxGachaResultViewModel(
        IReadOnlyList<GachaResultCellViewModel> CellViewModels,
        IReadOnlyList<PlayerResourceIconViewModel> ConvertedCellViewModels,
        IReadOnlyList<PlayerResourceIconViewModel> AvatarViewModels,
        PreConversionResourceExistenceFlag ExistsPreConversionResource,
        IReadOnlyList<UnreceivedRewardReasonType> UnreceivedRewardReasonTypeByDrawnResult,
        IReadOnlyList<ArtworkFragmentAcquisitionViewModel> ArtworkFragmentAcquisitionViewModels)
    {
        public static BoxGachaResultViewModel Empty { get; } = new (
            new List<GachaResultCellViewModel>(),
            new List<PlayerResourceIconViewModel>(),
            new List<PlayerResourceIconViewModel>(),
            PreConversionResourceExistenceFlag.False,
            new List<UnreceivedRewardReasonType>(),
            new List<ArtworkFragmentAcquisitionViewModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
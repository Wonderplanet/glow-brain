using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.GachaResult.Presentation
{
    public record GachaResultViewModel(
        GachaType GachaType,
        List<GachaResultCellViewModel> CellViewModels,
        List<PlayerResourceIconViewModel> ConvertedCellViewModels,
        List<PlayerResourceIconViewModel> AvatarViewModels,
        PreConversionResourceExistenceFlag ExistsPreConversionResource
        );
}

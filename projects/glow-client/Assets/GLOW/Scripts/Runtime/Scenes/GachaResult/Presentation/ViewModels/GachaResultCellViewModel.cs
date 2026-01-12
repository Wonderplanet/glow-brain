using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.GachaResult.Presentation
{
    public record GachaResultCellViewModel(
        PlayerResourceIconViewModel PlayerResourceIconViewModel,
        IsNewUnitBadge IsNewUnitBadge
    );
}

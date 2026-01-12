using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaSeries.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaSeries.Presentation.ViewModels
{
    public record EncyclopediaEnemyUnitListCellViewModel(
        MasterDataId MstEnemyId,
        EnemySmallIconViewModel Icon,
        EncyclopediaUnlockFlag IsUnlocked,
        NotificationBadge NewBadge);
}

using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EncyclopediaSeries.Domain.ValueObjects;

namespace GLOW.Scenes.EncyclopediaSeries.Domain.Models
{
    public record EncyclopediaSeriesEnemyListCellModel(
        MasterDataId MstEnemyId,
        EnemySmallIconModel Icon,
        EncyclopediaUnlockFlag IsUnlocked,
        NotificationBadge IsNew);
}

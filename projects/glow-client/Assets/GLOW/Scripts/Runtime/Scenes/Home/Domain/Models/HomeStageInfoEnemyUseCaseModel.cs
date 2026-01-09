using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeStageInfoEnemyUseCaseModel(
        MasterDataId MstEnemyId,
        CharacterName EnemyName,
        CharacterColor EnemyColor,
        CharacterUnitRoleType EnemyUnitRoleType,
        CharacterUnitKind EnemyUnitKind,
        UnitAssetKey EnemyIconAssetKey,
        SortOrder SortOrder);
}

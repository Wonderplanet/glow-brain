using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.AdventBattle.Domain.Model
{
    public record AdventBattleInfoEnemyModel(
        MasterDataId MstEnemyId,
        CharacterName EnemyName,
        CharacterColor EnemyColor,
        CharacterUnitRoleType EnemyUnitRoleType,
        CharacterUnitKind EnemyUnitKind,
        UnitAssetKey EnemyIconAssetKey,
        SortOrder SortOrder);
}
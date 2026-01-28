using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.AdventBattle.Presentation.ViewModel.AdventBattleInfo
{
    public record AdventBattleInfoEnemyViewModel(
        MasterDataId MstEnemyId,
        CharacterName EnemyName,
        CharacterColor EnemyColor,
        CharacterUnitRoleType EnemyUnitRoleType,
        CharacterUnitKind EnemyUnitKind,
        EnemyCharacterIconAssetPath EnemyIconAssetPath,
        SortOrder SortOrder
    );
}
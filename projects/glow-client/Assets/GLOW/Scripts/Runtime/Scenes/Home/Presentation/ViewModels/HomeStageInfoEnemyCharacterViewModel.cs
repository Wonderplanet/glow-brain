using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Presentation.ViewModels
{
    public record HomeStageInfoEnemyCharacterViewModel(
        EnemyCharacterIconAssetPath EnemyCharacterIconAssetPath,
        CharacterName CharacterName,
        CharacterColor CharacterColor,
        CharacterUnitRoleType CharacterUnitRoleType,
        CharacterUnitKind CharacterUnitKind,
        SortOrder SortOrder);
}

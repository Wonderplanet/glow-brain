using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PvpTop.Presentation.ViewModel
{
    public record PvpTopOpponentPartyUnitViewModel(
        CharacterIconAssetPath UnitIconAssetPath,
        CharacterUnitRoleType RoleType,
        CharacterColor Color,
        Rarity Rarity,
        UnitLevel Level,
        UnitGrade Grade)
    {
        public static PvpTopOpponentPartyUnitViewModel Empty { get; } = new(
            CharacterIconAssetPath.Empty,
            CharacterUnitRoleType.None,
            CharacterColor.None,
            Rarity.R,
            UnitLevel.Empty,
            UnitGrade.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}

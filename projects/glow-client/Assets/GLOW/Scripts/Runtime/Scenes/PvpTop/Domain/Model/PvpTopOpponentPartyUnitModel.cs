using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
namespace GLOW.Scenes.PvpTop.Domain
{
    public record PvpTopOpponentPartyUnitModel(
        CharacterIconAssetPath CharacterIconAssetPath,
        CharacterUnitRoleType RoleType,
        CharacterColor Color,
        Rarity Rarity,
        UnitLevel Level,
        UnitGrade Grade)
    {
        public static PvpTopOpponentPartyUnitModel Empty { get; } = new(
            CharacterIconAssetPath.Empty,
            CharacterUnitRoleType.None,
            CharacterColor.None,
            Rarity.R,
            UnitLevel.Empty,
            UnitGrade.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

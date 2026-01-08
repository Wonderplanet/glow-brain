using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.PartyFormation.Domain.Models
{
    public record PartyFormationPartySpecialRuleItemModel(
        UserDataId UserUnitId,
        InGameSpecialRuleAchievedFlag IsAchievedSpecialRule)
    {
        public static PartyFormationPartySpecialRuleItemModel Empty { get; } = new (
            UserDataId.Empty,
            InGameSpecialRuleAchievedFlag.True);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

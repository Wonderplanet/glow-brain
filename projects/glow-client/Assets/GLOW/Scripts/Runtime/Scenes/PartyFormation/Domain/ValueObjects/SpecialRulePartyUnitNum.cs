using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.PartyFormation.Domain.ValueObjects
{
    public record SpecialRulePartyUnitNum(ObscuredInt Value)
    {
        public static SpecialRulePartyUnitNum Empty { get; } = new(0);
        public static SpecialRulePartyUnitNum Min { get; } = new(1);
        public static SpecialRulePartyUnitNum Max { get; } = new(10);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsMax()
        {
            return Value == Max.Value;
        }

        public bool IsInRange(int index)
        {
            if (IsEmpty() || IsMax()) return true;
            return Value > index;
        }
    }
}

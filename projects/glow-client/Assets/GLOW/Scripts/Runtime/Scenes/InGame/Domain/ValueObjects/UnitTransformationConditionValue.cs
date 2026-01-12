using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record UnitTransformationConditionValue(ObscuredString Value)
    {
        public static UnitTransformationConditionValue Empty { get; } = new UnitTransformationConditionValue(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public CommonConditionValue ToCommonConditionValue()
        {
            return new CommonConditionValue(Value);
        }
    }
}

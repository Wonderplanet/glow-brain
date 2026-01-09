using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record AutoPlayerSequenceConditionValue(ObscuredString Value)
    {
        public static AutoPlayerSequenceConditionValue Empty { get; } = new(string.Empty);

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

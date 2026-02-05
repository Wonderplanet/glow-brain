using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record UnitAbilityParameter(ObscuredString Value)
    {
        public static UnitAbilityParameter Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public StateEffectParameter ToStateEffectParameter()
        {
            return new StateEffectParameter(int.Parse(Value));
        }

        public Percentage ToPercentage()
        {
            return new Percentage(int.Parse(Value));
        }

        public EffectiveCount ToEffectiveCount()
        {
            return new EffectiveCount(int.Parse(Value));
        }

        public EffectiveProbability ToEffectiveProbability()
        {
            return new EffectiveProbability(int.Parse(Value));
        }
        
        public StateEffectConditionValue ToStateEffectConditionValue()
        {
            return new StateEffectConditionValue(Value);
        }

        public CommonConditionValue ToCommonConditionValue()
        {
            return new CommonConditionValue(Value);
        }
    }
}

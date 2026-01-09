using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record StageRewardPercentage(ObscuredInt Value)
    {
        public static StageRewardPercentage Empty { get; } = new (0);
        
        public override string ToString()
        {
            return Value.ToString();
        }
    };
}

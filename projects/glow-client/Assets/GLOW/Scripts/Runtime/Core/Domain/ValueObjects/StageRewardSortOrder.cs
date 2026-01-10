using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record StageRewardSortOrder(ObscuredInt Value)
    {
        public static StageRewardSortOrder Empty { get; } = new (0);
        
        public override string ToString()
        {
            return Value.ToString();
        }
    };
}
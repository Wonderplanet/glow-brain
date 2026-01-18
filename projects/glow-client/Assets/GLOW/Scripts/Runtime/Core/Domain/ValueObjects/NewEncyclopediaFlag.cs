using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record NewEncyclopediaFlag(ObscuredBool Value)
    {
        public static NewEncyclopediaFlag False { get; } = new NewEncyclopediaFlag(false);
        public static NewEncyclopediaFlag True { get; } = new NewEncyclopediaFlag(true);

        public static implicit operator bool(NewEncyclopediaFlag flag)
        {
            return flag.Value;
        }
    }
}

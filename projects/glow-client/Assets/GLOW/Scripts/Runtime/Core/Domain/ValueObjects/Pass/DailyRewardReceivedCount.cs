using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pass
{
    public record DailyRewardReceivedCount(ObscuredInt Value)
    {
        public static DailyRewardReceivedCount Empty { get; } = new(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Pvp
{
    public record PvpRewardReceivedFlag(ObscuredBool Value)
    {
        public static PvpRewardReceivedFlag True { get; } = new PvpRewardReceivedFlag(true);
        public static PvpRewardReceivedFlag False { get; } = new PvpRewardReceivedFlag(false);

        public static implicit operator bool(PvpRewardReceivedFlag pvpRewardReceivedFlag) => pvpRewardReceivedFlag.Value;
    }
}
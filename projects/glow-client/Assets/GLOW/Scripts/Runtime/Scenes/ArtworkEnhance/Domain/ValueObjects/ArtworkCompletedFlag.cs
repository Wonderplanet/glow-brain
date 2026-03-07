using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects
{
    // 原画が完成済み(取得済み)かどうか
    public record ArtworkCompletedFlag(ObscuredBool Value)
    {
        public static ArtworkCompletedFlag False { get; } = new(false);
        public static ArtworkCompletedFlag True { get; } = new(true);

        public static implicit operator bool(ArtworkCompletedFlag completedFlag) => completedFlag.Value;
    }
}

using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public record ArtworkBonusHpInitializationResult(
        HP PlayerArtworkBonusHp,
        HP PvpOpponentArtworkBonusHp)
    {
        public static ArtworkBonusHpInitializationResult Empty { get; } = new (
            HP.Empty,
            HP.Empty);
    }
}

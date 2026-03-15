namespace GLOW.Core.Domain.ValueObjects.ArtworkEffect
{
    public record ArtworkEffectTargetFlag(bool Value)
    {
        public static ArtworkEffectTargetFlag True { get; } = new ArtworkEffectTargetFlag(true);
        public static ArtworkEffectTargetFlag False { get; } = new ArtworkEffectTargetFlag(false);

        public static implicit operator bool(ArtworkEffectTargetFlag flag) => flag.Value;
    }
}

namespace GLOW.Core.Domain.ValueObjects.ArtworkEffect
{
    public record ArtworkEffectActivationFlag(bool Value)
    {
        public static ArtworkEffectActivationFlag True { get; } = new ArtworkEffectActivationFlag(true);
        public static ArtworkEffectActivationFlag False { get; } = new ArtworkEffectActivationFlag(false);

        public static implicit operator bool(ArtworkEffectActivationFlag flag) => flag.Value;
    }
}

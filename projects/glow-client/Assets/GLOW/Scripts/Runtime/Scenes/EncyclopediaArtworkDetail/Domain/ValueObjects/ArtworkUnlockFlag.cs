namespace GLOW.Scenes.EncyclopediaArtworkDetail.Domain.ValueObjects
{
    public record ArtworkUnlockFlag(bool Value)
    {
        public static ArtworkUnlockFlag True { get; } = new ArtworkUnlockFlag(true);
        public static ArtworkUnlockFlag False { get; } = new ArtworkUnlockFlag(false);
        public static implicit operator bool(ArtworkUnlockFlag flag)
        {
            return flag.Value;
        }
    }
}

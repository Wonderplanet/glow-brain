namespace GLOW.Scenes.EncyclopediaArtworkDetail.Domain.ValueObjects
{
    public record EnableArtworkChangeFlag(bool Value)
    {
        public static implicit operator bool(EnableArtworkChangeFlag flag)
        {
            return flag.Value;
        }
    }
}

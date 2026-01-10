namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GachaDrawFromContentViewFlag(bool Value)
    {
        public static GachaDrawFromContentViewFlag True { get; } = new(true);
        public static GachaDrawFromContentViewFlag False { get; } = new(false);

        public static implicit operator bool(GachaDrawFromContentViewFlag flag) => flag?.Value ?? false;
    }
}

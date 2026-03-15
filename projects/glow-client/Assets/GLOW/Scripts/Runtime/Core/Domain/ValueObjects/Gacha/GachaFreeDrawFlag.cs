namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GachaFreeDrawFlag(bool Value)
    {
        public static GachaFreeDrawFlag True { get; } = new GachaFreeDrawFlag(true);
        public static GachaFreeDrawFlag False { get; } = new GachaFreeDrawFlag(false);

        public static implicit operator bool(GachaFreeDrawFlag flag)
        {
            return flag.Value;
        }
    }
}


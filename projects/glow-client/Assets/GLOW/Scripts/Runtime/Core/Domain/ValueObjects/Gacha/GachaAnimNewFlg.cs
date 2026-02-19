namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record GachaAnimNewFlg(bool Value)
    {
        public static GachaAnimNewFlg Empty { get; } = new(false);
    }
}

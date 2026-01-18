namespace GLOW.Scenes.PackShopProductInfo.Domain.ValueObjects
{
    public record IsTicketItemFlag(bool Value)
    {
        public static IsTicketItemFlag True { get; } = new IsTicketItemFlag(true);
        public static IsTicketItemFlag False { get; } = new IsTicketItemFlag(false);
        
        public static implicit operator bool(IsTicketItemFlag flag) => flag.Value;
    }
}
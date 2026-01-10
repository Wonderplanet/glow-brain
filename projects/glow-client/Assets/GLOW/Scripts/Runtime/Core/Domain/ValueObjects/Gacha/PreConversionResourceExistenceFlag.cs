namespace GLOW.Core.Domain.ValueObjects.Gacha
{
    public record PreConversionResourceExistenceFlag(bool Value)
    {
        public static PreConversionResourceExistenceFlag True { get; } = new(true);
        public static PreConversionResourceExistenceFlag False { get; } = new(false);

        public static implicit operator bool(PreConversionResourceExistenceFlag flag) => flag.Value;
    }
}

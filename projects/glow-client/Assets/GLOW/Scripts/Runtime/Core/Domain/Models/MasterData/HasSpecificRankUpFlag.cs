namespace GLOW.Core.Domain.Models
{
    public record HasSpecificRankUpFlag(bool Value)
    {
        public static HasSpecificRankUpFlag True { get; } = new(true);
        public static HasSpecificRankUpFlag False { get; } = new(false);

        public static  implicit operator bool(HasSpecificRankUpFlag flag) => flag.Value;
    }
}

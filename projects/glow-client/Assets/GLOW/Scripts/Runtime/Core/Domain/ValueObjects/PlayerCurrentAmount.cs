namespace GLOW.Core.Domain.ValueObjects
{
    public record PlayerCurrentAmount(long Value)
    {
        public static PlayerCurrentAmount Empty { get; } = new (0);

        public override string ToString()
        {
            return $"{Value:N0}";;
        }

        public string ToFormattedString()
        {
            return $"所持数 {Value:N0}";
        }

        public string ToFreeDiamondFormattedString()
        {
            return $"無償所持数 {Value:N0}";
        }

        public string ToPaidDiamondFormattedString()
        {
            return $"有償所持数 {Value:N0}";
        }
    }
}

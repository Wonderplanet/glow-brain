using System.Globalization;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record ProductResourceAmount(ObscuredInt Value)
    {
        public static ProductResourceAmount Empty { get; } = new(0);
        public static ProductResourceAmount Zero { get; } = new(0);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public override string ToString()
        {
            return Value.ToString();
        }

        public string ToStringWithSeparate()
        {
            return Value.ToString("N0", CultureInfo.InvariantCulture);
        }

        public string ToStringWithMultiplicationAndSeparate()
        {
            return $"×{Value:N0}";
        }

        public string ToStringWithMultiplication()
        {
            return $"×{Value}";
        }

        public PlayerResourceAmount ToPlayerResourceAmount()
        {
            return new PlayerResourceAmount(Value);
        }
    }
}

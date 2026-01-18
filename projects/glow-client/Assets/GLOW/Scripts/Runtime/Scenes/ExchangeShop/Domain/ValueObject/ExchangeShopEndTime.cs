using System;

namespace GLOW.Scenes.ExchangeShop.Domain.ValueObject
{
    public record ExchangeShopEndTime(DateTimeOffset Value)
    {
        public static ExchangeShopEndTime Empty { get; } = new ExchangeShopEndTime(DateTimeOffset.MinValue);
        public static ExchangeShopEndTime Unlimited { get; } = new ExchangeShopEndTime(DateTimeOffset.MaxValue);

        public bool IsUnlimited()
        {
            return ReferenceEquals(this, Unlimited);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

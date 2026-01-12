using System;

namespace GLOW.Scenes.ExchangeShop.Domain.ValueObject
{
    public record ExchangeShopStartTime(DateTimeOffset Value)
    {
        public static ExchangeShopStartTime Empty { get; } = new ExchangeShopStartTime(DateTimeOffset.MinValue);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

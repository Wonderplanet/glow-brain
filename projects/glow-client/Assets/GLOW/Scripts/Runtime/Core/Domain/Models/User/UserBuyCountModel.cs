using System;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record UserBuyCountModel(BuyStaminaAdCount DailyBuyStaminaAdCount, DateTimeOffset? DailyBuyStaminaAdAt)
    {
        public static UserBuyCountModel Empty { get; } = new (BuyStaminaAdCount.Empty, null);
    };
}

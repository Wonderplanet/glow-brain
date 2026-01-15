using GLOW.Core.Domain.Constants;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Shop
{
    public record SaleConditionValue(SaleCondition? Condition, ObscuredString Value)
    {
        public static SaleConditionValue Empty = new SaleConditionValue(null, string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

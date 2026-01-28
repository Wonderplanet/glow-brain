using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.StaminaRecover.Domain.ValueObject
{
    public record StaminaRecoveryAvailableStatus(
        StaminaRecoveryType StaminaRecoveryType,
        BuyStaminaAdCount BuyAdCount,
        ItemAmount ItemAmount)
    {
        public static StaminaRecoveryAvailableStatus Empty { get; } = new (
            StaminaRecoveryType.Item,
            BuyStaminaAdCount.Empty,
            ItemAmount.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

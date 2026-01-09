using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.StaminaRecover;

namespace GLOW.Scenes.StaminaRecover.Domain
{
    public record ReceivableStaminaTimeModel(
        StaminaRecoveryFlag StaminaRecoveryFlag,
        RemainingTimeSpan ReceivableStaminaTime)
    {
        public static ReceivableStaminaTimeModel Empty { get; } = new (
            StaminaRecoveryFlag.False, 
            RemainingTimeSpan.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
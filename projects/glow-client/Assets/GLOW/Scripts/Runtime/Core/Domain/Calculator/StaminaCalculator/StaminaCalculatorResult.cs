using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;

namespace GLOW.Core.Domain.Calculator
{
    public record StaminaCalculatorResult(
        Stamina CurrentStamina,
        RemainStaminaRecoverSecond RemainUpdatingStaminaRecoverSecond)
    {
        public static StaminaCalculatorResult Empty { get; } = new StaminaCalculatorResult(Stamina.Empty, RemainStaminaRecoverSecond.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

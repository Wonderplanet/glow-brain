using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Pass;

namespace GLOW.Scenes.PassShop.Presentation.ViewModel
{
    public record PassEffectViewModel(
        ShopPassEffectType PassEffectType,
        PassEffectValue PassEffectValue)
    {
        public static PassEffectViewModel Empty { get; } = new(
            ShopPassEffectType.StaminaAddRecoveryLimit,
            PassEffectValue.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
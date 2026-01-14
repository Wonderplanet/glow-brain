using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.IdleIncentive;
using GLOW.Core.Domain.ValueObjects.Pass;

namespace GLOW.Scenes.PassShop.Domain.Model
{
    public record PassEffectModel(
        ShopPassEffectType PassEffectType,
        PassEffectValue PassEffectValue)
    {
        public static PassEffectModel Empty { get; } = new(
            ShopPassEffectType.StaminaAddRecoveryLimit,
            PassEffectValue.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public bool IsValid()
        {
            return !PassEffectValue.IsZero();
        }

        public Stamina GetStaminaAddRecoveryLimit()
        {
            return PassEffectValue.ToStamina();
        }

        public IdleIncentiveReceiveCount GetIdleIncentiveReceiveCount()
        {
            return PassEffectValue.ToIdleIncentiveReceiveCount();
        }
    }
}
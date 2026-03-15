using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;

namespace GLOW.Scenes.PassShop.Domain.Model
{
    public record PurchasePassUseCaseModel(
        PassProductName PassProductName,
        RemainingTimeSpan PassEffectValidRemainingTime)
    {
        public static PurchasePassUseCaseModel Empty { get; } = new (
            PassProductName.Empty,
            RemainingTimeSpan.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
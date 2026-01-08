using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;

namespace GLOW.Scenes.PassShop.Domain.Model
{
    public record HeldAdSkipPassInfoModel(
        PassProductName PassProductName,
        RemainingTimeSpan HeldRemainingTimeSpan)
    {
        public static HeldAdSkipPassInfoModel Empty { get; } = new HeldAdSkipPassInfoModel(
            PassProductName.Empty,
            RemainingTimeSpan.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
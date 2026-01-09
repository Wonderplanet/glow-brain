using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;

namespace GLOW.Scenes.PassShop.Presentation.ViewModel
{
    public record HeldAdSkipPassInfoViewModel(
        PassProductName PassProductName,
        RemainingTimeSpan HeldRemainingTimeSpan)
    {
        public static HeldAdSkipPassInfoViewModel Empty { get; } = new HeldAdSkipPassInfoViewModel(
            PassProductName.Empty,
            RemainingTimeSpan.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
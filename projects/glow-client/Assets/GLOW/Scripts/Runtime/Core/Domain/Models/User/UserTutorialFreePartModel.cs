using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record UserTutorialFreePartModel(TutorialFunctionName TutorialFunctionName)
    {
        public static UserTutorialFreePartModel Empty { get; } = new UserTutorialFreePartModel(TutorialFunctionName.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

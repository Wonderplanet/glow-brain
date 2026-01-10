using GLOW.Modules.TutorialTapIcon.Presentation.ValueObject;

namespace GLOW.Modules.TutorialTapIcon.Presentation.ViewModel
{
    public record TutorialTapIconViewModel(
        TutorialTapIconPosition TutorialTapIconPosition,
        TutorialTapEffectPosition TutorialTapEffectPosition,
        ReverseFlag ReverseFlag)
    {
        public static TutorialTapIconViewModel Empty { get; } = new TutorialTapIconViewModel(
            TutorialTapIconPosition.Empty,
            TutorialTapEffectPosition.Empty,
            ReverseFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

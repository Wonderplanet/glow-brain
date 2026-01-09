using GLOW.Modules.Tutorial.Domain.ValueObject;

namespace GLOW.Modules.TutorialMessageBox.Presentation.ViewModel
{
    public record TutorialMessageBoxViewModel(
        TutorialMessageBoxText Text,
        TutorialMessageBoxPositionY PositionY);
}

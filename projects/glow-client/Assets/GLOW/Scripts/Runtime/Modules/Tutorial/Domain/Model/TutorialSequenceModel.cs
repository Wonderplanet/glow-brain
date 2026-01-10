using GLOW.Modules.Tutorial.Domain.ValueObject;
using GLOW.Modules.InvertMaskView.Domain.ValueObject;

namespace GLOW.Modules.Tutorial.Domain.Model
{
    public record TutorialSequenceModel(
        TutorialSequenceId TutorialSequenceId,
        TutorialCallbackActionIdentifier TutorialCallbackActionIdentifier,
        TutorialInvertMaskPositionIdentifier TutorialInvertMaskPositionIdentifier,
        AllowTapOnlyInvertMaskedAreaFlag AllowTapOnlyInvertMaskedAreaFlag,
        DisplayTutorialTapIconFlag DisplayTutorialTapIconFlag,
        TutorialMessageBoxPositionY TutorialMessageBoxPositionY,
        TutorialMessageBoxText TutorialMessageBoxText);
}

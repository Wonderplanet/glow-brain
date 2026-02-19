using GLOW.Modules.InvertMaskView.Domain.ValueObject;
using GLOW.Modules.InvertMaskView.Presentation.ValueObject;

namespace GLOW.Modules.InvertMaskView.Presentation.ViewModel
{
    public record InvertMaskViewModel(
        AllowTapOnlyInvertMaskedAreaFlag AllowTapOnlyInvertMaskedAreaFlag,
        InvertMaskPosition InvertMaskPosition,
        InvertMaskSize InvertMaskSize);
}

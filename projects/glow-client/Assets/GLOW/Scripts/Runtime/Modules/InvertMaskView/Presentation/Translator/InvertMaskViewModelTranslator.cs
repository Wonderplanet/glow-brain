using GLOW.Modules.InvertMaskView.Domain.ValueObject;
using GLOW.Modules.InvertMaskView.Presentation.ValueObject;
using GLOW.Modules.InvertMaskView.Presentation.ViewModel;

namespace GLOW.Modules.InvertMaskView.Presentation.Translator
{
    public class InvertMaskViewModelTranslator
    {
        public static InvertMaskViewModel Translate(
            AllowTapOnlyInvertMaskedAreaFlag allowTapOnlyInvertMaskedAreaFlag,
            InvertMaskPosition invertMaskPosition,
            InvertMaskSize invertMaskSize)
        {
            return new InvertMaskViewModel(
                allowTapOnlyInvertMaskedAreaFlag,
                invertMaskPosition,
                invertMaskSize
                );
        }
    }
}

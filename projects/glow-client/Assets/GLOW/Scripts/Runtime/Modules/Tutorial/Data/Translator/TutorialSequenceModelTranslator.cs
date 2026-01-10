using System.Collections.Generic;
using GLOW.Modules.InvertMaskView.Domain.ValueObject;
using GLOW.Modules.Tutorial.Data.Data;
using GLOW.Modules.Tutorial.Domain.Model;
using GLOW.Modules.Tutorial.Domain.ValueObject;

namespace GLOW.Modules.Tutorial.Data.Translator
{
    public class TutorialSequenceModelTranslator
    {
        public static List<TutorialSequenceModel> TranslateToTutorialSequenceModel(TutorialSequenceDataList dataList)
        {
            var list = new List<TutorialSequenceModel>();
            foreach (var data in dataList.Entities)
            {
                var model = new TutorialSequenceModel(
                    new TutorialSequenceId(data.SequenceId),
                    string.IsNullOrEmpty(data.CallbackActionIdentifier) ? TutorialCallbackActionIdentifier.Empty : new TutorialCallbackActionIdentifier(data.CallbackActionIdentifier),
                    string.IsNullOrEmpty(data.InvertMaskPositionIdentifier) ? TutorialInvertMaskPositionIdentifier.Empty : new TutorialInvertMaskPositionIdentifier(data.InvertMaskPositionIdentifier),
                    new AllowTapOnlyInvertMaskedAreaFlag(data.ShouldTapOnlyInvertMask),
                    new DisplayTutorialTapIconFlag(data.DisplayTapIcon),
                    new TutorialMessageBoxPositionY(data.TextPositionY),
                    string.IsNullOrEmpty(data.Text) ? TutorialMessageBoxText.Empty : new TutorialMessageBoxText(data.Text));
                list.Add(model);
            }

            return list;
        }
    }
}

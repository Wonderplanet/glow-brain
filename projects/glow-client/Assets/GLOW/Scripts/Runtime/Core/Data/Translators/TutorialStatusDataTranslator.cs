using GLOW.Core.Domain.Models.Tutorial;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class TutorialStatusDataTranslator
    {
        public static TutorialStatusModel ToTutorialStatusModel(string tutorialStatusData)
        {
            return new TutorialStatusModel(new TutorialFunctionName(tutorialStatusData));
        }
    }
}

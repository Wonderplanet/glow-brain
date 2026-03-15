using System.Linq;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.GachaContent.Presentation.Translator;
using GLOW.Scenes.GachaList.Domain.Model;
using GLOW.Scenes.GachaList.Presentation.ViewModels;

namespace GLOW.Scenes.GachaList.Presentation.Translator
{
    public class GachaListViewModelTranslator
    {
        public static GachaListViewModel Translate(GachaListUseCaseModel useCaseModel)
        {
            var tutorialViewModel = useCaseModel.TutorialGachaListElementUseCaseModel.IsEmpty()
                ? GachaListElementViewModel.Empty
                : GachaListElementTranslator.TranslateElement(useCaseModel.TutorialGachaListElementUseCaseModel);

            var stepRewardViewModels = useCaseModel.StepRewardModels
                .Select(model => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(model))
                .ToList();

            return new GachaListViewModel(
                useCaseModel.InitialShowOprGachaId,
                tutorialViewModel,
                useCaseModel.GachaListUseCaseElementModels.Select(GachaListElementTranslator.TranslateElement).ToList(),
                stepRewardViewModels
            );
        }
    }
}

using System.Linq;
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

            return new GachaListViewModel(
                useCaseModel.InitialShowOprGachaId,
                tutorialViewModel,
                useCaseModel.GachaListUseCaseElementModels.Select(GachaListElementTranslator.TranslateElement).ToList()
            );
        }

    }
}

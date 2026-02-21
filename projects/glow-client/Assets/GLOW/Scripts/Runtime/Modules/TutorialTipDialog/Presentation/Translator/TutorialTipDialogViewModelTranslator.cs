using System.Collections.Generic;
using GLOW.Modules.TutorialTipDialog.Domain.Models;
using GLOW.Modules.TutorialTipDialog.Presentation.ViewModel;

namespace GLOW.Modules.TutorialTipDialog.Presentation.Translator
{
    public class TutorialTipDialogViewModelTranslator
    {
        public static IReadOnlyList<TutorialTipDialogViewModel> TranslateToTutorialTipDialogViewModels(TutorialTipDialogUseCaseModel useCaseModel)
        {
            var models = new List<TutorialTipDialogViewModel>();

            foreach (var model in useCaseModel.TutorialTipModels)
            {
                models.Add(new TutorialTipDialogViewModel(
                    model.Title,
                    model.AssetPath));
            }

            return models;
        }
    }
}

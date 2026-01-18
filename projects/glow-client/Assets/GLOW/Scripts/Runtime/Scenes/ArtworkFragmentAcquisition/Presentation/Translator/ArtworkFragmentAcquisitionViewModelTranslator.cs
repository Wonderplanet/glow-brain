using System.Collections.Generic;
using GLOW.Scenes.ArtworkFragment.Presentation.Translator;
using GLOW.Scenes.ArtworkFragmentAcquisition.Domain.Models;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.Translator
{
    public class ArtworkFragmentAcquisitionViewModelTranslator
    {
        public static IReadOnlyList<ArtworkFragmentAcquisitionViewModel> ToTranslate(
            IReadOnlyList<ArtworkFragmentAcquisitionModel> models)
        {
            var viewModels = new List<ArtworkFragmentAcquisitionViewModel>();

            foreach (var model in models)
            {
                viewModels.Add(new ArtworkFragmentAcquisitionViewModel(
                    ArtworkPanelViewModelTranslator.ToTranslate(model.ArtworkPanelModel),
                    model.AcquiredArtworkFragmentPositions,
                    model.ArtworkName,
                    model.Description,
                    model.IsCompleted,
                    model.AddHp));
            }

            return viewModels;
        }
    }
}

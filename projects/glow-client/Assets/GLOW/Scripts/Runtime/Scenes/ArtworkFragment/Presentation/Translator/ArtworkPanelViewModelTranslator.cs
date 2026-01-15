using System.Collections.Generic;
using GLOW.Scenes.ArtworkFragment.Domain.Model;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkFragment.Presentation.Translator
{
    public class ArtworkPanelViewModelTranslator
    {
        public static ArtworkPanelViewModel ToTranslate(ArtworkPanelModel model)
        {
            return new ArtworkPanelViewModel(
                ToArtworkFragmentPanelViewModel(model)
                );
        }

        public static ArtworkFragmentPanelViewModel ToArtworkFragmentPanelViewModel(ArtworkPanelModel model)
        {
            return new ArtworkFragmentPanelViewModel(
                model.AssetPath,
                ToArtworkFragmentViewModels(model.ArtworkFragmentModels));
        }

        static List<ArtworkFragmentViewModel> ToArtworkFragmentViewModels(IReadOnlyList<ArtworkFragmentModel> models)
        {
            var viewModels = new List<ArtworkFragmentViewModel>();

            int count = 1;
            foreach (var model in models)
            {
                viewModels.Add(new ArtworkFragmentViewModel(
                    model.PositionNum,
                    new ArtworkFragmentNum(count),
                    model.IsUnlock));

                count++;
            }

            return viewModels;
        }
    }
}

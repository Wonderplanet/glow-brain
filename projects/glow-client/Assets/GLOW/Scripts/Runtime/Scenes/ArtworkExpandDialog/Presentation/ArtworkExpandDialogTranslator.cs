using System.Collections.Generic;
using GLOW.Scenes.ArtworkExpandDialog.Domain.Models;
using GLOW.Scenes.ArtworkExpandDialog.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Presentation.ViewModels;

namespace GLOW.Scenes.ArtworkExpandDialog.Presentation
{
    public class ArtworkExpandDialogTranslator
    {
        public static ArtworkExpandDialogViewModel Translate(ArtworkExpandDialogModel model)
        {
            var fragmentViewModel = new List<ArtworkFragmentViewModel>();

            int count = 1;
            foreach (var fragment in model.ArtworkFragmentModels)
            {
                fragmentViewModel.Add(new ArtworkFragmentViewModel(
                    fragment.PositionNum,
                    new ArtworkFragmentNum(count),
                    fragment.IsUnlock));

                count++;
            }

            return new ArtworkExpandDialogViewModel(
                model.Name,
                model.Description,
                model.AssetPath,
                new ArtworkFragmentPanelViewModel(
                    model.AssetPath,
                    model.IsCompleted,
                    fragmentViewModel));
        }
    }
}

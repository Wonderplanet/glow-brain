using System.Linq;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Presentation.ViewModels;

namespace GLOW.Core.Presentation.Views.RotationBanner.HomeMain
{
    public static class HomeMainKomaViewModelTranslator
    {
        public static HomeMainKomaViewModel Translate(HomeMainKomaUseCaseModel model)
        {
            var unitViewModels = model.HomeMainKomaUnitUseCaseModels
                .Select(m => new HomeMainKomaUnitViewModel(m.MstUnitId, m.PlaceIndex, m.HomeMainKomaUnitAssetPath))
                .ToList();
            return new HomeMainKomaViewModel(model.HomeMainKomaPatternAssetPath, unitViewModels);
        }
    }
}

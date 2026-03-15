using System.Linq;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Presentation.ViewModels;

namespace GLOW.Core.Presentation.Views.RotationBanner.HomeMain
{
    public static class HomeMainKomaSettingViewModelTranslator
    {
        public static HomeMainKomaSettingViewModel Translate(HomeMainKomaSettingUseCaseModel model)
        {
            var patternViewModels = model.HomeMainKomaPatternUseCaseModels
                .Select(TranslateHomeMainKomaPatternViewModel)
                .ToList();
            return new HomeMainKomaSettingViewModel(model.InitialSelectedIndex, patternViewModels);
        }

        static HomeMainKomaPatternViewModel TranslateHomeMainKomaPatternViewModel(HomeMainKomaPatternUseCaseModel model)
        {
            var unitViewModels = model.HomeMainKomaUnitUseCaseModels
                .Select(TranslateHomeMainKomaUnitViewModel)
                .ToList();

            return new HomeMainKomaPatternViewModel(
                model.MstKomaPatterId,
                model.Name,
                model.AssetPath,
                unitViewModels
            );
        }

        static HomeMainKomaUnitViewModel TranslateHomeMainKomaUnitViewModel(HomeMainKomaUnitUseCaseModel model)
        {
            return new HomeMainKomaUnitViewModel(model.MstUnitId, model.PlaceIndex, model.HomeMainKomaUnitAssetPath);
        }

    }
}

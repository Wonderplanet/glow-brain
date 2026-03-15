using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.HomeMainKomaSettingFilter.Presentation;
using GLOW.Scenes.UnitList.Domain.Models;

namespace GLOW.Core.Presentation.Views.RotationBanner.HomeMain
{
    public static class HomeMainKomaSettingFilterViewModelTranslator
    {
        public static HomeMainKomaSettingFilterViewModel Translate(HomeMainKomaSettingFilterUseCaseModel model)
        {
            return new HomeMainKomaSettingFilterViewModel(
                new FilterSeriesModel(model.FilteredMstSeriesIds),
                model.SeriesTitles);
        }
    }
}
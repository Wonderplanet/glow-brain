using System.Linq;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.HomeMainKomaSettingUnitSelect.Presentation;

namespace GLOW.Scenes.HomeMenuSetting.Presentation.Translator
{
    public class HomeMainKomaSettingUnitSelectViewModelTranslator
    {
        public static HomeMainKomaSettingUnitSelectViewModel Translate(HomeMainKomaSettingUnitSelectUseCaseModel model)
        {
            var units = model.Units.Select(TranslateItem).ToList();
            return new HomeMainKomaSettingUnitSelectViewModel(units);
        }

        static HomeMainKomaSettingUnitSelectItemViewModel TranslateItem(
            HomeMainKomaSettingUnitSelectItemUseCaseModel itemModel)
        {
            return new HomeMainKomaSettingUnitSelectItemViewModel(
                itemModel.MstUnitId,
                itemModel.AssetPath,
                itemModel.Status);
        }
    }
}

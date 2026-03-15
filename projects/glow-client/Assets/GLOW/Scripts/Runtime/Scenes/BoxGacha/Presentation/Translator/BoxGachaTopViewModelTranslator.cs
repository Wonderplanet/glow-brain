using GLOW.Scenes.BoxGacha.Domain.Model;
using GLOW.Scenes.BoxGacha.Presentation.ViewModel;

namespace GLOW.Scenes.BoxGacha.Presentation.Translator
{
    public static class BoxGachaTopViewModelTranslator
    {
        public static BoxGachaTopViewModel ToBoxGachaTopViewModel(BoxGachaTopModel model)
        {
            return new BoxGachaTopViewModel(
                model.MstBoxGachaId,
                model.BoxGachaName,
                model.DisplayDecoUnitFirst,
                model.DisplayDecoEnemyUnitSecond,
                model.KomaBackgroundAssetPath,
                BoxGachaInfoViewModelTranslator.ToBoxGachaInfoViewModel(model.BoxGachaInfoModel));
        }
    }
}
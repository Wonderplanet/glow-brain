using GLOW.Scenes.UnitReceive.Domain.Model;
using GLOW.Scenes.UnitReceive.Presentation.ViewModel;

namespace GLOW.Scenes.UnitReceive.Presentation.Translator
{
    public class UnitReceiveViewModelTranslator
    {
        public static UnitReceiveViewModel ToReceiveViewModel(
            UnitReceiveModel model)
        {
            return new UnitReceiveViewModel(
                model.CharacterName,
                model.RoleType,
                model.CharacterColor,
                model.Rarity,
                model.UnitCutInKomaAssetPath,
                model.UnitImageAssetPath,
                model.SeriesLogoImagePath,
                model.SpeechBalloonText);
        }
    }
}
using GLOW.Scenes.EncyclopediaUnitSpecialAttack.Domain.Models;
using GLOW.Scenes.EncyclopediaUnitSpecialAttack.Presentation.ViewModels;

namespace GLOW.Scenes.EncyclopediaUnitSpecialAttack.Presentation.Translators
{
    public class UnitSpecialAttackPreviewViewModelTranslator
    {
        public static UnitSpecialAttackPreviewViewModel Translate(UnitSpecialAttackPreviewModel model)
        {
            return new UnitSpecialAttackPreviewViewModel(
                model.UnitColor,
                model.UnitImageAssetPath,
                model.UnitAssetKey,
                model.ChargeTime,
                model.ActionDuration,
                model.IsRight);
        }
    }
}

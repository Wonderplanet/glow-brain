using GLOW.Core.Domain.Models;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Core.Presentation.Translators
{
    public static class CharacterIconViewModelTranslator
    {
        public static CharacterIconViewModel Translate(CharacterIconModel model)
        {
            return new CharacterIconViewModel(
                model.IconAssetPath,
                model.Role,
                model.Color,
                model.Rarity,
                model.Level,
                model.SummonCost,
                model.Grade,
                model.Hp,
                model.AttackPower,
                model.AttackRangeType,
                model.MoveSpeed);
        }
    }
}

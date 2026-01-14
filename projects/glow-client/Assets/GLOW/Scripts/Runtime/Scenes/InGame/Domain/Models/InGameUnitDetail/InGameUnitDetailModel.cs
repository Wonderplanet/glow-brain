using System.Collections.Generic;
using GLOW.Scenes.UnitEnhance.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Models.InGameUnitDetail
{
    public record InGameUnitDetailModel(
        InGameUnitDetailInfoModel Info,
        InGameUnitDetailSpecialAttackModel SpecialAttack,
        IReadOnlyList<UnitEnhanceAbilityModel> AbilityList,
        InGameUnitDetailStatusModel Status);
}

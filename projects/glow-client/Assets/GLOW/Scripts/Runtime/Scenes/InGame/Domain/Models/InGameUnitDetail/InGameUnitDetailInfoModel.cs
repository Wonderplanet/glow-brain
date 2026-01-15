using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.InGameUnitDetail
{
    public record InGameUnitDetailInfoModel(
        CharacterIconModel IconModel,
        CharacterName Name,
        IReadOnlyList<StateEffectType> StateEffectTypeList,
        EventBonusPercentage BonusPercentage);
}

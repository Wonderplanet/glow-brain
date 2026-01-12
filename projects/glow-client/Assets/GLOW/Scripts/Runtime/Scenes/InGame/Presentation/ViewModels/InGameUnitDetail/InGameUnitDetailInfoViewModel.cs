using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.InGame.Presentation.ViewModels.InGameUnitDetail
{
    public record InGameUnitDetailInfoViewModel(
        CharacterIconViewModel IconViewModel,
        CharacterName Name,
        IReadOnlyList<StateEffectType> StateEffectTypeList,
        EventBonusPercentage BonusPercentage);
}

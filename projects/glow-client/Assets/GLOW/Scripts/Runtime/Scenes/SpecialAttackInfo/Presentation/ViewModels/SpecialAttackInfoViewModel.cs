using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.SpecialAttackInfo.Presentation.ViewModels
{
    public record SpecialAttackInfoViewModel(
        SpecialAttackName Name,
        SpecialAttackInfoDescription Description,
        SpecialAttackCoolTime CoolTime,
        CharacterName CharacterName,
        IReadOnlyList<SpecialAttackInfoGradeViewModel> RankViewModelList);
}

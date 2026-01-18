using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;

namespace GLOW.Scenes.UserLevelUp.Presentation.ViewModel
{
    public record UserLevelUpInfoViewModel(
        UserLevelUpResultViewModel UserLevelUpEffectModel,
        IReadOnlyList<UserExpGainViewModel> UserExpGainModels,
        RelativeUserExp CurrentExp,
        RelativeUserExp NextLevelExp)
    {
        public static UserLevelUpInfoViewModel Empty { get; } = new(
            UserLevelUpResultViewModel.Empty,
            new List<UserExpGainViewModel>(),
            RelativeUserExp.Empty,
            RelativeUserExp.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsLevelUp()
        {
            return !UserLevelUpEffectModel.IsEmpty();
        }
    }
}

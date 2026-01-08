using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.UserLevelUp.Presentation.ViewModel
{
    public record UserLevelUpResultViewModel(
        UserLevel NextUserLevel,
        IReadOnlyList<PlayerResourceIconViewModel> PlayerResourceIconViewModels,
        Stamina BeforeMaxStamina,
        Stamina AfterMaxStamina,
        bool IsLevelMax)
    {
        public static UserLevelUpResultViewModel Empty { get; } = new (
            UserLevel.Empty,
            new List<PlayerResourceIconViewModel>(),
            Stamina.Empty,
            Stamina.Empty,
            false);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record UserLevelUpEffectModel(
        UserLevel UserLevel,
        IReadOnlyList<PlayerResourceModel> PlayerResourceResultModels,
        Stamina BeforeMaxStamina,
        Stamina AfterMaxStamina,
        bool IsLevelMax)
    {
        public static UserLevelUpEffectModel Empty { get; } = new (
            UserLevel.Empty,
            new List<PlayerResourceModel>(),
            Stamina.Empty, 
            Stamina.Empty, 
            false);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
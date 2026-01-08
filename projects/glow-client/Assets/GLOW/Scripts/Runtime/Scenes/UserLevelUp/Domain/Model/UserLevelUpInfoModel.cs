using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UserLevelUp.Domain.ValueObject;

namespace GLOW.Scenes.UserLevelUp.Domain.Model
{
    public record UserLevelUpInfoModel(
        UserLevelUpEffectModel UserLevelUpEffectModel,
        IReadOnlyList<UserExpGainModel> UserExpGainModels,
        RelativeUserExp CurrentExp,
        RelativeUserExp NextLevelExp,
        ExpChangedFlag IsExpChange)
    {
        public static UserLevelUpInfoModel Empty { get; } = new(
            UserLevelUpEffectModel.Empty,
            new List<UserExpGainModel>(),
            RelativeUserExp.Empty,
            RelativeUserExp.Empty,
            ExpChangedFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
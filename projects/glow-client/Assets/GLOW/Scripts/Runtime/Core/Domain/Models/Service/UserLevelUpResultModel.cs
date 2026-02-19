using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UserLevelUp.Domain.ValueObject;

namespace GLOW.Core.Domain.Models
{
    public record UserLevelUpResultModel(
        UserExp BeforeExp,
        UserExp AfterExp,
        IReadOnlyList<UsrLevelRewardResultModel> Rewards)
    {
        public static UserLevelUpResultModel Empty { get; } = new(
            UserExp.Empty,
            UserExp.Empty,
            new List<UsrLevelRewardResultModel>());
        
        public ExpChangedFlag IsExpChange()
        {
            return new(BeforeExp != AfterExp);
        }
    }
}

using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.InGameSpecialRule.Domain.ValueObjects
{
    public record InGameSpecialRuleTimeLimit(TimeLimit TimeLimit, StageEndType EndType)
    {
        public static InGameSpecialRuleTimeLimit Empty { get; } = new(TimeLimit.Empty, StageEndType.Victory);

        public bool IsDefeat => EndType == StageEndType.Defeat;
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

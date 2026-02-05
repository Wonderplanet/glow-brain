using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    /// <summary>
    /// ユーザー経験値の増加とレベルアップを表すモデル
    /// </summary>
    public record UserExpGainModel(
        UserLevel Level,
        RelativeUserExp StartExp,
        RelativeUserExp EndExp,
        RelativeUserExp NextLevelExp)
    {
        public static UserExpGainModel Empty { get; } = new(
            UserLevel.Empty,
            RelativeUserExp.Empty,
            RelativeUserExp.Empty,
            RelativeUserExp.Empty);
    }
}

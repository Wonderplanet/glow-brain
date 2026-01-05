using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.BattleResult.Presentation.ViewModels
{
    /// <summary>
    /// ユーザー経験値の増加とレベルアップを表すモデル
    /// </summary>
    public record UserExpGainViewModel(
        UserLevel Level,
        RelativeUserExp StartExp,
        RelativeUserExp EndExp,
        RelativeUserExp NextLevelExp,
        bool IsLevelUp // レベルアップしてこのレベルになったか
    )
    {
        // 経験値を獲得しているか
        public bool IsExpGain { get => StartExp.Value < EndExp.Value; }
    }
}

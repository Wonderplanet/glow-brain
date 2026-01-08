namespace GLOW.Scenes.AdventBattleRanking.Presentation.ValueObjects
{
    public record AdventBattleRankingUserAvatarPath(string Value)
    {
        public static AdventBattleRankingUserAvatarPath Empty { get; } = new AdventBattleRankingUserAvatarPath(string.Empty);
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }
}
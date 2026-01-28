namespace GLOW.Scenes.AdventBattle.Presentation.ValueObject
{
    public record HighScoreRewardCellIndex(int Value)
    {
        public static HighScoreRewardCellIndex Empty { get; } = new HighScoreRewardCellIndex(0);

        public bool IsZero()
        {
            return Value == 0;
        }
    }
}
namespace GLOW.Core.Domain.ValueObjects
{
    public record MissionBonusPointAssetKey
    {
        string DailyValue { get; } = "bonus_point_daily";
        string WeeklyValue { get; } = "bonus_point_weekly";
        string BeginnerValue { get; } = "bonus_point_beginner";

        public PlayerResourceAssetKey ToDailyMissionBonusPointResourceAssetKey()
        {
            return new PlayerResourceAssetKey(DailyValue);
        }
        
        public PlayerResourceAssetKey ToWeeklyMissionBonusPointResourceAssetKey()
        {
            return new PlayerResourceAssetKey(WeeklyValue);
        }

        public PlayerResourceAssetKey ToBeginnerMissionBonusPointResourceAssetKey()
        {
            return new PlayerResourceAssetKey(BeginnerValue);
        }
    }
}
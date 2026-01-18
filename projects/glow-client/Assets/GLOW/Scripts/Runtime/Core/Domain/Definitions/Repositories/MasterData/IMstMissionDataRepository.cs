namespace GLOW.Core.Domain.Repositories
{
    // 別タイミングで削除する
    public interface IMstMissionDataRepository :
        IMstMissionDailyDataRepository,
        IMstMissionWeeklyDataRepository,
        IMstMissionAchievementDataRepository,
        IMstMissionBeginnerDataRepository,
        IMstMissionEventDataRepository,
        IMstMissionLimitedDataRepository,
        IMstMissionRewardDataRepository,
        IMstComebackBonusDataRepository
    {
    }
}

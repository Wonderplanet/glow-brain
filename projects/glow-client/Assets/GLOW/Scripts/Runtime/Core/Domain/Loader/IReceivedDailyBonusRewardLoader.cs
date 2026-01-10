namespace GLOW.Core.Domain.Loader
{
    public interface IReceivedDailyBonusRewardLoader
    {
        void LoadReceivedDailyBonusRewards();
        void LoadReceivedEventDailyBonusRewards();
        void LoadReceivedComebackDailyBonusRewards();
    }
}
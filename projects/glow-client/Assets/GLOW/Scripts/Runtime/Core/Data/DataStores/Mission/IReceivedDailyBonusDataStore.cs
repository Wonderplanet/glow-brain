using GLOW.Core.Data.Data;

namespace GLOW.Core.Data.DataStores.Mission
{
    public interface IReceivedDailyBonusDataStore
    {
        void Load();
        void Save(DailyBonusRewardData[] dailyBonusRewards);
        void Delete();
        DailyBonusRewardData[] Get();
        bool IsExist();
    }
}
using GLOW.Core.Data.Data;

namespace GLOW.Core.Data.DataStores.Mission
{
    public interface IReceivedEventDailyBonusDataStore
    {
        void Load();
        void Save(EventDailyBonusRewardData[] eventDailyBonusRewards);
        void Delete();
        EventDailyBonusRewardData[] Get();
        bool IsExist();
    }
}
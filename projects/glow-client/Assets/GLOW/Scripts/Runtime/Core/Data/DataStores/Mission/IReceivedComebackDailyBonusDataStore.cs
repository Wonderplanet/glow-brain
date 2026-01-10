using GLOW.Core.Data.Data;

namespace GLOW.Core.Data.DataStores.Mission
{
    public interface IReceivedComebackDailyBonusDataStore
    {
        void Load();
        void Save(ComebackBonusRewardData[] comebackDailyBonusRewards);
        void Delete();
        ComebackBonusRewardData[] Get();
        bool IsExist();
    }
}
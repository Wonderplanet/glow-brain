using GLOW.Core.Data.Data;
using Newtonsoft.Json;
using Runtime.PlayerPrefs;
using WPFramework.Modules.Log;

namespace GLOW.Core.Data.DataStores.Mission
{
    public class ReceivedDailyBonusDataStore : IReceivedDailyBonusDataStore
    {
        const string PrefsKey = "GLOW.Data.DataStores.ReceivedDailyBonusDataStore.KEY";

        DailyBonusRewardData[] _dailyBonusRewards;
        
        void IReceivedDailyBonusDataStore.Load()
        {
            ApplicationLog.Log(nameof(IReceivedDailyBonusDataStore), "Load ReceivedDailyBonusData");
            
            if (!EncryptionPlayerPrefs.HasKey(PrefsKey))
            {
                return;
            }
            
            var jsonString = EncryptionPlayerPrefs.GetString(PrefsKey);
            _dailyBonusRewards = JsonConvert.DeserializeObject<DailyBonusRewardData[]>(jsonString);
        }

        void IReceivedDailyBonusDataStore.Save(DailyBonusRewardData[] dailyBonusRewards)
        {
            ApplicationLog.Log(nameof(IReceivedDailyBonusDataStore), "Save ReceivedDailyBonusData");
            
            var jsonString = JsonConvert.SerializeObject(dailyBonusRewards);
            EncryptionPlayerPrefs.SetString(PrefsKey, jsonString);
            EncryptionPlayerPrefs.Save();
        }

        void IReceivedDailyBonusDataStore.Delete()
        {
            ApplicationLog.Log(nameof(IReceivedDailyBonusDataStore), "Delete ReceivedDailyBonusData");
            
            _dailyBonusRewards = null;
            
            if (!EncryptionPlayerPrefs.HasKey(PrefsKey))
            {
                return;
            }
            
            // PlayerPreferenceのDeleteAllを呼ぶと消える
            EncryptionPlayerPrefs.DeleteKey(PrefsKey);
            EncryptionPlayerPrefs.Save();
        }

        DailyBonusRewardData[] IReceivedDailyBonusDataStore.Get()
        {
            ApplicationLog.Log(nameof(IReceivedDailyBonusDataStore), "Get ReceivedDailyBonusData");
            return _dailyBonusRewards;
        }

        bool IReceivedDailyBonusDataStore.IsExist()
        {
            ApplicationLog.Log(nameof(IReceivedDailyBonusDataStore), "IsExist ReceivedDailyBonusData");

            return EncryptionPlayerPrefs.HasKey(PrefsKey);
        }
    }
}
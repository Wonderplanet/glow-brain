using GLOW.Core.Data.Data;
using Newtonsoft.Json;
using Runtime.PlayerPrefs;
using WPFramework.Modules.Log;

namespace GLOW.Core.Data.DataStores.Mission
{
    public class ReceivedComebackDailyBonusDataStore : IReceivedComebackDailyBonusDataStore
    {
        const string PrefsKey = "GLOW.Data.DataStores.ReceivedComebackDailyBonusDataStore.KEY";

        ComebackBonusRewardData[] _comebackDailyBonusRewards;
        
        void IReceivedComebackDailyBonusDataStore.Load()
        {
            ApplicationLog.Log(nameof(IReceivedComebackDailyBonusDataStore), "Load ReceivedDailyBonusData");
            
            if (!EncryptionPlayerPrefs.HasKey(PrefsKey))
            {
                return;
            }
            
            var jsonString = EncryptionPlayerPrefs.GetString(PrefsKey);
            _comebackDailyBonusRewards = JsonConvert.DeserializeObject<ComebackBonusRewardData[]>(jsonString);
        }

        void IReceivedComebackDailyBonusDataStore.Save(ComebackBonusRewardData[] comebackDailyBonusRewards)
        {
            ApplicationLog.Log(nameof(IReceivedComebackDailyBonusDataStore), "Save ReceivedDailyBonusData");
            
            var jsonString = JsonConvert.SerializeObject(comebackDailyBonusRewards);
            EncryptionPlayerPrefs.SetString(PrefsKey, jsonString);
            EncryptionPlayerPrefs.Save();
        }

        void IReceivedComebackDailyBonusDataStore.Delete()
        {
            ApplicationLog.Log(nameof(IReceivedComebackDailyBonusDataStore), "Delete ReceivedDailyBonusData");
            
            _comebackDailyBonusRewards = null;
            
            if (!EncryptionPlayerPrefs.HasKey(PrefsKey))
            {
                return;
            }
            
            // PlayerPreferenceのDeleteAllを呼ぶと消える
            EncryptionPlayerPrefs.DeleteKey(PrefsKey);
            EncryptionPlayerPrefs.Save();
        }

        ComebackBonusRewardData[] IReceivedComebackDailyBonusDataStore.Get()
        {
            ApplicationLog.Log(nameof(IReceivedComebackDailyBonusDataStore), "Get ReceivedDailyBonusData");
            
            return _comebackDailyBonusRewards;
        }

        bool IReceivedComebackDailyBonusDataStore.IsExist()
        {
            ApplicationLog.Log(nameof(IReceivedComebackDailyBonusDataStore), "IsExist ReceivedDailyBonusData");

            return EncryptionPlayerPrefs.HasKey(PrefsKey);
        }
    }
}
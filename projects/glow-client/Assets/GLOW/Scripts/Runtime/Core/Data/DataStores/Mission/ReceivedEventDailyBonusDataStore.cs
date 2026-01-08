using GLOW.Core.Data.Data;
using Newtonsoft.Json;
using Runtime.PlayerPrefs;
using WPFramework.Modules.Log;

namespace GLOW.Core.Data.DataStores.Mission
{
    public class ReceivedEventDailyBonusDataStore : IReceivedEventDailyBonusDataStore
    {
        const string PrefsKey = "GLOW.Data.DataStores.ReceivedEventDailyBonusDataStore.KEY";

        EventDailyBonusRewardData[] _eventDailyBonusRewards;
        
        void IReceivedEventDailyBonusDataStore.Load()
        {
            ApplicationLog.Log(nameof(IReceivedDailyBonusDataStore), "Load ReceivedDailyBonusData");
            
            if (!EncryptionPlayerPrefs.HasKey(PrefsKey))
            {
                return;
            }
            
            var jsonString = EncryptionPlayerPrefs.GetString(PrefsKey);
            _eventDailyBonusRewards = JsonConvert.DeserializeObject<EventDailyBonusRewardData[]>(jsonString);
        }

        void IReceivedEventDailyBonusDataStore.Save(EventDailyBonusRewardData[] eventDailyBonusRewards)
        {
            ApplicationLog.Log(nameof(IReceivedDailyBonusDataStore), "Save ReceivedDailyBonusData");
            
            var jsonString = JsonConvert.SerializeObject(eventDailyBonusRewards);
            EncryptionPlayerPrefs.SetString(PrefsKey, jsonString);
            EncryptionPlayerPrefs.Save();
        }

        void IReceivedEventDailyBonusDataStore.Delete()
        {
            ApplicationLog.Log(nameof(IReceivedDailyBonusDataStore), "Delete ReceivedDailyBonusData");
            
            _eventDailyBonusRewards = null;
            
            if (!EncryptionPlayerPrefs.HasKey(PrefsKey))
            {
                return;
            }
            
            // PlayerPreferenceのDeleteAllを呼ぶと消える
            EncryptionPlayerPrefs.DeleteKey(PrefsKey);
            EncryptionPlayerPrefs.Save();
        }

        EventDailyBonusRewardData[] IReceivedEventDailyBonusDataStore.Get()
        {
            ApplicationLog.Log(nameof(IReceivedDailyBonusDataStore), "Get ReceivedDailyBonusData");
            return _eventDailyBonusRewards;
        }

        bool IReceivedEventDailyBonusDataStore.IsExist()
        {
            ApplicationLog.Log(nameof(IReceivedDailyBonusDataStore), "IsExist ReceivedDailyBonusData");

            return EncryptionPlayerPrefs.HasKey(PrefsKey);
        }
    }
}
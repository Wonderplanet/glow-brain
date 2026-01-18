using GLOW.Core.Data.Data.User;
using Newtonsoft.Json;
using Runtime.PlayerPrefs;
using WPFramework.Modules.Log;

namespace GLOW.Core.Data.DataStores
{
    public class SpecialAttackCutInLogLocalDataStore : ISpecialAttackCutInLogLocalDataStore
    {
        const string PreferenceKey = "GLOW.Data.DataStores.SpecialAttackCutInLogLocalDataStore.KEY";
        
        SpecialAttackCutInLogData ISpecialAttackCutInLogLocalDataStore.Load()
        {
            ApplicationLog.Log(nameof(ISpecialAttackCutInLogLocalDataStore), "Load UserPropertyData");
            if (!EncryptionPlayerPrefs.HasKey(PreferenceKey))
            {
                return null;
            }
            
            var jsonString = EncryptionPlayerPrefs.GetString(PreferenceKey);
            return JsonConvert.DeserializeObject<SpecialAttackCutInLogData>(jsonString);
        }
        
        public void Save(SpecialAttackCutInLogData specialAttackCutInLogData)
        {
            ApplicationLog.Log(nameof(ISpecialAttackCutInLogLocalDataStore), "Save UserPropertyData");
            
            var jsonString = JsonConvert.SerializeObject(specialAttackCutInLogData);
            EncryptionPlayerPrefs.SetString(PreferenceKey, jsonString);
            EncryptionPlayerPrefs.Save();
        }
    }
}
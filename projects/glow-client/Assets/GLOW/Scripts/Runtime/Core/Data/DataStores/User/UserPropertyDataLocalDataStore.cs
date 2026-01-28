using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.Data.User;
using GLOW.Modules.GameOption.Domain.Constants;
using Newtonsoft.Json;
using Runtime.PlayerPrefs;
using WPFramework.Modules.Log;

namespace GLOW.Core.Data.DataStores
{
    public class UserPropertyDataLocalDataStore : IUserPropertyDataStore
    {
        const string PrefsKey = "GLOW.Data.DataStores.UserPropertyDataLocalDataStore.KEY";
        const string HasFirstDamageSetting = "GLOW.Data.DataStores.UserPropertyDataLocalDataStore.HasFirstDamageSetting";

        UserPropertyData _userPropertyData;

        async UniTask IUserPropertyDataStore.Load(CancellationToken cancellationToken)
        {
            ApplicationLog.Log(nameof(UserPropertyDataLocalDataStore), "Load UserPropertyData");
            
            // 順番依存1
            _userPropertyData = Create();
            
            if (!EncryptionPlayerPrefs.HasKey(HasFirstDamageSetting))
            {
                // 順番依存2
                // 副作用 : ダメージ表示設定がされていない場合は設定したとみなして1とする
                EncryptionPlayerPrefs.SetInt(HasFirstDamageSetting, 1);
            }
            
            await UniTask.CompletedTask;
        }

        void IUserPropertyDataStore.Save(UserPropertyData userPropertyData)
        {
            ApplicationLog.Log(nameof(UserPropertyDataLocalDataStore), "Save UserPropertyData");

            var jsonString = JsonConvert.SerializeObject(userPropertyData);
            EncryptionPlayerPrefs.SetString(PrefsKey, jsonString);
            EncryptionPlayerPrefs.Save();

            _userPropertyData = userPropertyData;
        }

        void IUserPropertyDataStore.Delete()
        {
            EncryptionPlayerPrefs.DeleteKey(PrefsKey);
            EncryptionPlayerPrefs.Save();
        }

        UserPropertyData IUserPropertyDataStore.Get()
        {
            return _userPropertyData;
        }

        UserPropertyData Create()
        {
            if (!EncryptionPlayerPrefs.HasKey(PrefsKey))
            {
                return new UserPropertyData(
                    false,
                    false,
                    SpecialAttackCutInPlayType.On,
                    false,
                    true,
                    true);
            }

            var jsonString = EncryptionPlayerPrefs.GetString(PrefsKey);
            
            // ダメージ表示設定がされていない場合
            if (!EncryptionPlayerPrefs.HasKey(HasFirstDamageSetting))
            {
                // 古いデータ形式として読み込み、ダメージ表示設定をtrueで上書きする
                var existData = JsonConvert.DeserializeObject<UserPropertyData>(jsonString);
                return new UserPropertyData(
                    existData.IsBgmMute,
                    existData.IsSeMute,
                    existData.SpecialAttackCutInPlayType,
                    existData.IsPushOff,
                    existData.IsTwoRowDeck,
                    true);
            }
            
            return JsonConvert.DeserializeObject<UserPropertyData>(jsonString);
        }
    }
}

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

        UserPropertyData _userPropertyData;

        async UniTask IUserPropertyDataStore.Load(CancellationToken cancellationToken)
        {
            ApplicationLog.Log(nameof(UserPropertyDataLocalDataStore), "Load UserPropertyData");
            if (!EncryptionPlayerPrefs.HasKey(PrefsKey))
            {
                _userPropertyData = new UserPropertyData(
                    false,
                    false,
                    SpecialAttackCutInPlayType.On,
                    false,
                    true,
                    true);

                await UniTask.CompletedTask;
                return;
            }

            var jsonString = EncryptionPlayerPrefs.GetString(PrefsKey);
            _userPropertyData = JsonConvert.DeserializeObject<UserPropertyData>(jsonString);

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
    }
}

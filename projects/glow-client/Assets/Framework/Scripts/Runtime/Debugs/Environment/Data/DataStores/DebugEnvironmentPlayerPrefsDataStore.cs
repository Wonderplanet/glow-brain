using System.Threading;
using Cysharp.Threading.Tasks;
using Newtonsoft.Json;
using UnityEngine;
using WPFramework.Debugs.Environment.Data.Data;
using WPFramework.Modules.Environment;

namespace WPFramework.Debugs.Environment.Data.DataStores
{
    public sealed class DebugEnvironmentPlayerPrefsDataStore : IDebugEnvironmentSelectDataStore, IDebugEnvironmentSpecifiedDomainDataStore
    {
        const string EnvironmentLastKey = "WPFramework.Debugs.Environment.Data.DataStores.DebugEnvironmentPlayerPrefsDataStore.EnvironmentLastKey";
        const string EnvironmentInputDomainKey = "WPFramework.Debugs.Environment.Data.DataStores.DebugEnvironmentPlayerPrefsDataStore.EnvironmentInputDomainKey";

        EnvironmentListData _environmentListData;
        EnvironmentData _lastEnvironmentData;

        DebugEnvironmentSpecifiedDomainData _debugEnvironmentSpecifiedDomainData;

        async UniTask IDebugEnvironmentSelectDataStore.Load(CancellationToken cancellationToken)
        {
            if (PlayerPrefs.HasKey(EnvironmentLastKey))
            {
                var jsonString = PlayerPrefs.GetString(EnvironmentLastKey);
                _lastEnvironmentData = JsonConvert.DeserializeObject<EnvironmentData>(jsonString);
            }

            await UniTask.CompletedTask;
        }

        void IDebugEnvironmentSelectDataStore.Save(EnvironmentData environmentData)
        {
            var jsonString = JsonConvert.SerializeObject(environmentData);
            PlayerPrefs.SetString(EnvironmentLastKey, jsonString);
            _lastEnvironmentData = environmentData;
        }

        EnvironmentData IDebugEnvironmentSelectDataStore.Get()
        {
            return _lastEnvironmentData;
        }

        async UniTask IDebugEnvironmentSpecifiedDomainDataStore.Load(CancellationToken cancellationToken)
        {
            if (PlayerPrefs.HasKey(EnvironmentInputDomainKey))
            {
                var jsonString = PlayerPrefs.GetString(EnvironmentInputDomainKey);
                _debugEnvironmentSpecifiedDomainData = JsonConvert.DeserializeObject<DebugEnvironmentSpecifiedDomainData>(jsonString);
            }

            await UniTask.CompletedTask;
        }

        void IDebugEnvironmentSpecifiedDomainDataStore.Save(DebugEnvironmentSpecifiedDomainData debugEnvironmentSpecifiedDomainData)
        {
            var jsonString = JsonConvert.SerializeObject(debugEnvironmentSpecifiedDomainData);
            PlayerPrefs.SetString(EnvironmentInputDomainKey, jsonString);
            _debugEnvironmentSpecifiedDomainData = debugEnvironmentSpecifiedDomainData;
        }

        DebugEnvironmentSpecifiedDomainData IDebugEnvironmentSpecifiedDomainDataStore.Get()
        {
            return _debugEnvironmentSpecifiedDomainData;
        }

        void IDebugEnvironmentSpecifiedDomainDataStore.Delete()
        {
            PlayerPrefs.DeleteKey(EnvironmentInputDomainKey);
            _debugEnvironmentSpecifiedDomainData = null;
        }
    }
}

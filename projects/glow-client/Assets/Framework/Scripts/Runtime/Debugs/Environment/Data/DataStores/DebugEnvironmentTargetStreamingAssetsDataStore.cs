using System.IO;
using System.Text;
using System.Threading;
using Cysharp.Threading.Tasks;
using Newtonsoft.Json;
using UnityEngine;
using WonderPlanet.StorageSupporter;
using WonderPlanet.UnityStandard.Module.File;
using WPFramework.Debugs.Environment.Data.Data;

namespace WPFramework.Debugs.Environment.Data.DataStores
{
    public sealed class DebugEnvironmentTargetStreamingAssetsDataStore : IDebugEnvironmentTargetDataStore
    {
        const string DebugEnvironmentRecommendFileName = "target_environment.json";

        DebugEnvironmentTargetData _debugEnvironmentTargetData;

        async UniTask IDebugEnvironmentTargetDataStore.Load(CancellationToken cancellationToken)
        {
            try
            {
                var data = await FileSupport.ReadAllBytesAsync(
                    cancellationToken,
                    PathFormatter.Combine(Application.streamingAssetsPath, DebugEnvironmentRecommendFileName));
                var jsonString = Encoding.UTF8.GetString(data);
                _debugEnvironmentTargetData = JsonConvert.DeserializeObject<DebugEnvironmentTargetData>(jsonString);
            }
            catch (FileNotFoundException)
            {
                _debugEnvironmentTargetData = new DebugEnvironmentTargetData(string.Empty);
            }
            catch (UnityWebRequestException)
            {
                _debugEnvironmentTargetData = new DebugEnvironmentTargetData(string.Empty);
            }
        }

        DebugEnvironmentTargetData IDebugEnvironmentTargetDataStore.Get()
        {
            return _debugEnvironmentTargetData;
        }
    }
}

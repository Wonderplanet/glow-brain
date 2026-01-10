using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Debugs.Command.Domains.Definitions.Repositories;
using WonderPlanet.CultureSupporter.Time;
using WPFramework.Domain.Modules;
//using WonderPlanet.RemoteNotificationBridge;
//using WonderPlanet.RemoteNotificationBridge.FirebaseCludMessaging;
using Zenject;

namespace GLOW.Debugs.Command.Domains.UseCase
{
    public sealed class DebugCommandUseCases
    {
        //[Inject] RemoteNotificationCenter RemoteNotificationCenter { get; }
        [Inject] IDebugAdvertisingRepository DebugAdvertisingRepository { get; }
        [Inject] IEnvironmentResolver EnvironmentResolver { get; }

        public DebugTopUseCaseModel GetDebugUseCaseModel()
        {
            var envName = new DebugCommandEnvName(EnvironmentResolver.Resolve().EnvironmentName);
            // NOTE: TimeProvider経由で取得した時間を渡す（キャリブレーション済み）
            return new DebugTopUseCaseModel(TimeProvider.DateTimeOffsetSource.Now, envName);
        }

        public async UniTask CopyDeviceTokenToClipboard(CancellationToken cancellationToken)
        {
            // var token = await RemoteNotificationCenter
            //     .GetAgent<FirebaseCloudMessagingNotificationAgent>()
            //     .GetToken(cancellationToken);
            // UnityEngine.GUIUtility.systemCopyBuffer = token;
            await UniTask.CompletedTask;
        }

        public DebugAdUnitSearchUseCaseModel SearchAdUnit(string uniqueId)
        {
            var adUnit = DebugAdvertisingRepository.GetAdUnit(uniqueId);
            return adUnit == null ? null : new DebugAdUnitSearchUseCaseModel(adUnit.AdUnit, adUnit.UniqueId);
        }
    }
}

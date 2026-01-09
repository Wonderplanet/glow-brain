using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Constants;
using UnityHTTPLibrary;
using WPFramework.Debugs.Environment.Domain.Modules;
using WPFramework.Debugs.Environment.Domain.Repositories;
using WPFramework.Debugs.Environment.Modules;
using WPFramework.Domain.Models;
using WPFramework.Domain.Modules;
using WPFramework.Domain.Translators;
using WPFramework.Modules.Environment;
using Zenject;

namespace GLOW.Debugs.Environment.Data.Services
{
    public class DebugEnvironmentService : IDebugEnvironmentService
    {
        const string RemoteEnvFilePath = "env";
        const string RemoteEnvFileExtension = ".data";
        const string LocalEnvFileName = "env.json";
        const string LocalEnvFilePath = "Environment/" + LocalEnvFileName;

        [Inject] IEnvironmentHostResolver EnvironmentHostResolver { get; }
        [Inject] IEnvironmentDataParser EnvironmentDataParser { get; }
        [Inject] IDebugEnvironmentSpecifiedDomainRepository DebugEnvironmentSpecifiedDomainRepository { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        [Inject] EnvironmentCoordinator EnvironmentCoordinator { get; }
        [Inject] IEnvironmentTranslator EnvironmentTranslator { get; }
        [Inject] IEnvironmentListTranslator EnvironmentListTranslator { get; }
        [InjectOptional] ITLSCertificateHandler TLSCertificateHandler { get; }
        [InjectOptional] INetworkEnvironmentErrorHandler NetworkEnvironmentErrorHandler { get; }

        async UniTask IDebugEnvironmentService.FetchEnvironment(CancellationToken cancellationToken)
        {
            var applicationVersion = SystemInfoProvider.GetApplicationSystemInfo().Version;
            var protocols = new List<IEnvironmentProtocol>()
            {
                new NetworkEnvironmentProtocol(
                    settings: new NetworkEnvironmentProtocol.Settings(
                        Credentials.EnvironmentDataKey,
                        RemoteEnvFileExtension,
                        EnvironmentHostResolver.Resolve().Uri,
                        RemoteEnvFilePath,
                        applicationVersion),
                    environmentDataParser: EnvironmentDataParser,
                    certificateHandler: TLSCertificateHandler,
                    errorHandler: NetworkEnvironmentErrorHandler),
                new StreamingAssetEnvironmentProtocol(
                    new StreamingAssetEnvironmentProtocol.Settings(LocalEnvFilePath),
                    EnvironmentDataParser),
                new ResourcesEnvironmentProtocol(
                    new ResourcesEnvironmentProtocol.Settings(LocalEnvFilePath),
                    EnvironmentDataParser),
                new DebugSpecifiedApiDomainEnvironmentProtocol(DebugEnvironmentSpecifiedDomainRepository.Get()),
            };

            await EnvironmentCoordinator.Fetch(cancellationToken, protocols.ToArray());
        }

        EnvironmentModel IDebugEnvironmentService.FindConnectionEnvironment()
        {
            return EnvironmentTranslator.TranslateToModel(EnvironmentCoordinator.FindConnectionEnvironment());
        }

        bool IDebugEnvironmentService.ChangeConnectionEnvironment(string environmentName)
        {
            return EnvironmentCoordinator.ChangeConnectionEnvironment(environmentName);
        }

        EnvironmentListModel IDebugEnvironmentService.FetchEnvironmentList()
        {
            return EnvironmentListTranslator.TranslateToModel(EnvironmentCoordinator.GetEnvironmentList());
        }
    }
}

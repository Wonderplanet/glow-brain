using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Constants;
using GLOW.Core.Domain.Services;
using UnityHTTPLibrary;
using WPFramework.Domain.Models;
using WPFramework.Domain.Modules;
using WPFramework.Domain.Translators;
using WPFramework.Modules.Environment;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public class EnvironmentService : IEnvironmentService
    {
        const string RemoteEnvFilePath = "env";
        const string RemoteEnvFileExtension = ".data";

        [Inject] IEnvironmentHostResolver EnvironmentHostResolver { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        [Inject] IEnvironmentDataParser EnvironmentDataParser { get; }
        [Inject] EnvironmentCoordinator EnvironmentCoordinator { get; }
        [Inject] IEnvironmentTranslator EnvironmentTranslator { get; }
        [InjectOptional] ITLSCertificateHandler TLSCertificateHandler { get; }
        [InjectOptional] INetworkEnvironmentErrorHandler NetworkEnvironmentErrorHandler { get; }

        public async UniTask FetchEnvironment(CancellationToken cancellationToken)
        {
            // NOTE: 環境設定の取得処理を指定して実行
            var applicationVersion = SystemInfoProvider.GetApplicationSystemInfo().Version;
            var protocols = new List<IEnvironmentProtocol>()
            {
                new NetworkEnvironmentProtocol(
                    settings: new NetworkEnvironmentProtocol.Settings(
                        FixedIdentifier: Credentials.EnvironmentDataKey,
                        EnvironmentListFileExtension: RemoteEnvFileExtension,
                        EnvironmentHost: EnvironmentHostResolver.Resolve().Uri,
                        EnvironmentPath: RemoteEnvFilePath,
                        ApplicationVersion: applicationVersion),
                    environmentDataParser: EnvironmentDataParser,
                    certificateHandler: TLSCertificateHandler,
                    errorHandler: NetworkEnvironmentErrorHandler),
            };

            await EnvironmentCoordinator.Fetch(cancellationToken, protocols.ToArray());
        }

        EnvironmentModel IEnvironmentService.FindConnectionEnvironment()
        {
            return EnvironmentTranslator.TranslateToModel(EnvironmentCoordinator.FindConnectionEnvironment());
        }
    }
}
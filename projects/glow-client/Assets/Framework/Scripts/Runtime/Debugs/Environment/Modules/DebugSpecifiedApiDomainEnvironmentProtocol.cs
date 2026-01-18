using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using WPFramework.Debugs.Environment.Domain.Models;
using WPFramework.Modules.Environment;

namespace WPFramework.Debugs.Environment.Modules
{
    public sealed class DebugSpecifiedApiDomainEnvironmentProtocol : IEnvironmentProtocol
    {
        public int Priority => 10000;

        readonly DebugEnvironmentSpecifiedDomainModel _debugEnvironmentSpecifiedDomainModel;

        public DebugSpecifiedApiDomainEnvironmentProtocol(DebugEnvironmentSpecifiedDomainModel debugEnvironmentSpecifiedDomainModel)
        {
            _debugEnvironmentSpecifiedDomainModel = debugEnvironmentSpecifiedDomainModel;
        }

        async UniTask<EnvironmentListData> IEnvironmentProtocol.FetchEnvironmentList(CancellationToken cancellationToken)
        {
            var environment = _debugEnvironmentSpecifiedDomainModel?.SpecifiedEnvironment;
            if (environment == null)
            {
                return new EnvironmentListData(Array.Empty<EnvironmentData>());
            }

            var specifiedEnvironment = new EnvironmentData(
                environment.Env,
                environment.Name,
                environment.Description,
                environment.Api,
                environment.AssetCdn,
                environment.MasterCdn,
                environment.WebCdn,
                environment.AnnouncementCdn,
                environment.BannerCdn,
                environment.AgreementCdn);
            return await UniTask.FromResult(new EnvironmentListData(new[] { specifiedEnvironment }));
        }

        public void Dispose()
        {
        }
    }
}

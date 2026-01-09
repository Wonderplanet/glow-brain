using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Debugs.AdminDebug.Domain.Data;
using GLOW.Debugs.AdminDebug.Domain.Models;
using GLOW.Debugs.Command.Domains.UseCase;
using WonderPlanet.CultureSupporter.Time;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Debugs.AdminDebug.Domain.UseCases
{
    public sealed class AdminDebugUseCases
    {
        [Inject] IAdminDebugMenuService AdminDebugMenuService { get; }
        [Inject] IEnvironmentResolver EnvironmentResolver { get; }

        public async UniTask<AdminDebugMenuCommandListModel> GetCommandList(CancellationToken cancellationToken)
        {
            return await AdminDebugMenuService.List(cancellationToken);
        }

        public async UniTask ExecuteCommand(CancellationToken cancellationToken, string command)
        {
            await AdminDebugMenuService.Execute(cancellationToken, command);
        }

        public DebugTopUseCaseModel GetUseCaseModel()
        {
            var envName = new DebugCommandEnvName(EnvironmentResolver.Resolve().EnvironmentName);
            // NOTE: TimeProvider経由で取得した時間を渡す（キャリブレーション済み）
            return new DebugTopUseCaseModel(TimeProvider.DateTimeOffsetSource.Now, envName);
        }
    }
}

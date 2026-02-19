using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Debugs.AdminDebug.Data.DataStores;
using GLOW.Debugs.AdminDebug.Domain.Data;
using GLOW.Debugs.AdminDebug.Domain.Models;
using UnityHTTPLibrary;
using WPFramework.Exceptions.Mappers;
using Zenject;

namespace GLOW.Debugs.AdminDebug.Data.Services
{
    public sealed class AdminDebugMenuService : IAdminDebugMenuService
    {
        [Inject] AdminDebugMenuApi AdminDebugMenuApi { get; }
        [Inject] IServerErrorExceptionMapper ServerErrorExceptionMapper { get; }

        async UniTask<AdminDebugMenuCommandListModel> IAdminDebugMenuService.List(CancellationToken cancellationToken)
        {
            try
            {
                var debugMenuListResultData = await AdminDebugMenuApi.DebugCommandList(cancellationToken);
                var debugMenuCommandListModel = new AdminDebugMenuCommandListModel(
                    debugMenuListResultData.DebugCommand
                        .Select(debugCommand =>
                            new AdminDebugMenuCommandModel(
                                debugCommand.Command,
                                debugCommand.Name,
                                debugCommand.Description))
                        .ToArray());
                return debugMenuCommandListModel;
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask IAdminDebugMenuService.Execute(CancellationToken cancellationToken, string command)
        {
            try
            {
                // NOTE: HEADOKなので戻り値は不要
                await AdminDebugMenuApi.DebugCommandExecute(cancellationToken, command);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }
    }
}

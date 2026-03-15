using System.Collections.Generic;
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
                                debugCommand.Description,
                                TranslateParameters(debugCommand.RequiredParameters)))
                        .ToArray());
                return debugMenuCommandListModel;
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask IAdminDebugMenuService.Execute(
            CancellationToken cancellationToken,
            string command,
            Dictionary<string, object> parameters)
        {
            try
            {
                // NOTE: HEADOKなので戻り値は不要
                await AdminDebugMenuApi.DebugCommandExecute(
                    cancellationToken, command, parameters);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        static Dictionary<string, AdminDebugParameterDefinitionModel> TranslateParameters(
            Dictionary<string, AdminDebugParameterDefinition> parameters)
        {
            if (parameters == null) return null;
            return parameters.ToDictionary(
                kvp => kvp.Key,
                kvp => new AdminDebugParameterDefinitionModel(
                    kvp.Value.Type,
                    kvp.Value.Min,
                    kvp.Value.Max,
                    kvp.Value.Description));
        }
    }
}

using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Debugs.AdminDebug.Domain.Models;

namespace GLOW.Debugs.AdminDebug.Domain.Data
{
    public interface IAdminDebugMenuService
    {
        UniTask<AdminDebugMenuCommandListModel> List(CancellationToken cancellationToken);
        UniTask Execute(CancellationToken cancellationToken, string command, Dictionary<string, object> parameters);
    }
}

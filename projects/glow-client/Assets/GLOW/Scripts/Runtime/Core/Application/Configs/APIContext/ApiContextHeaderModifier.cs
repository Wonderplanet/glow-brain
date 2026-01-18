using GLOW.Core.Domain.Modules.Network;
using UnityHTTPLibrary;
using Zenject;

namespace GLOW.Core.Application.Configs.APIContext
{
    public class ApiContextHeaderModifier : IApiContextHeaderModifier
    {
        [Inject] ICommonRequestHeaderAssignor CommonRequestHeaderAssignor { get; }

        void IApiContextHeaderModifier.Configure(ServerApi context)
        {
            CommonRequestHeaderAssignor.SetRequestHeaders(context);
        }
    }
}

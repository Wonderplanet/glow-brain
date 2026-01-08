using UnityHTTPLibrary;

namespace GLOW.Core.Domain.Modules.Network
{
    public interface IAgreementRequestHeaderAssignor
    {
        void SetRequestHeaders(ServerApi context);
    }
}

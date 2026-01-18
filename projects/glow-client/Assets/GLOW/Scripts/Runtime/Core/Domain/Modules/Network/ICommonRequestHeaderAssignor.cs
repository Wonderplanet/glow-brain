using UnityHTTPLibrary;

namespace GLOW.Core.Domain.Modules.Network
{
    public interface ICommonRequestHeaderAssignor
    {
        void SetRequestHeaders(ServerApi context);
    }
}

using UnityHTTPLibrary;

namespace GLOW.Core.Domain.Modules.Network
{
    public interface IApiContextHeaderModifier
    {
        void Configure(ServerApi context);
    }
}
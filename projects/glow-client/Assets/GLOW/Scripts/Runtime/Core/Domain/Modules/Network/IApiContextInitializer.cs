using UnityHTTPLibrary;

namespace GLOW.Core.Domain.Modules.Network
{
    public interface IApiContextInitializer
    {
        void Initialize(ServerApi context, ApiContextInitializeSettings contextInitializeSettings);
    }
}
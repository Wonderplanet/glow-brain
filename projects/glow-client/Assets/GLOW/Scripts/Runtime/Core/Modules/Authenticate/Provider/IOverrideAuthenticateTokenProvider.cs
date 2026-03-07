using System.Threading;
using Cysharp.Threading.Tasks;
using UnityHTTPLibrary.Authenticate.Provider;
using UnityHTTPLibrary.Authenticate.Session;

namespace GLOW.Core.Modules.Authenticate.Provider
{
    public interface IOverrideAuthenticateTokenProvider : IAuthenticationProvider
    {
        bool ExistsToken();
        UniTask OverrideAuthenticateToken(string token);
        UniTask<ApiSession> Authenticate(
            CancellationToken cancellationToken, 
            string deviceUniqueIdentifier,
            object optionalData = null);
    }
}

using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using UnityHTTPLibrary.Authenticate;
using UnityHTTPLibrary.Authenticate.Session;

namespace GLOW.Core.Modules.Authenticate
{
    public interface IOverrideAuthenticateToken : IAuthenticator
    {
        bool ExistsToken();
        UniTask OverrideAuthenticateToken(string token);
        UniTask<ApiSession> Authenticate(
            CancellationToken cancellationToken, 
            string deviceUniqueIdentifier,
            object optionalData = null);
    }
}

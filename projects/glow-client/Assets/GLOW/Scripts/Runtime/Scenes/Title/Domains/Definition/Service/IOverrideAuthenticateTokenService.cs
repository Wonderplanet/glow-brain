using System.Threading;
using Cysharp.Threading.Tasks;
using WPFramework.Domain.Models;
using WPFramework.Domain.Services;

namespace GLOW.Scenes.Title.Domains.Definition.Service
{
    public interface IOverrideAuthenticateTokenService : IAuthenticateService
    {
        bool ExistsToken();
        UniTask OverrideAuthenticateToken(CancellationToken cancellationToken, string token);
        UniTask<AuthorizationModel> Authenticate(CancellationToken cancellationToken, string deviceUniqueIdentifier);
    }
}

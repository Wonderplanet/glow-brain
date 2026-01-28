using System.Threading;
using Cysharp.Threading.Tasks;
using WPFramework.Domain.Models;

namespace WPFramework.Domain.Services
{
    public interface IAuthenticateService
    {
        UniTask<AuthorizationModel> Authenticate(CancellationToken cancellationToken);
        UniTask DeleteAuthenticationData(CancellationToken cancellationToken);
    }
}

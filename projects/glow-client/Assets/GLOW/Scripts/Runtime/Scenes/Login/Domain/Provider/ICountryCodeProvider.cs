using System.Threading;
using Cysharp.Threading.Tasks;

namespace GLOW.Scenes.Login.Domain.Provider
{
    public interface ICountryCodeProvider
    {
        UniTask<string> GetCountryCodeAsync(CancellationToken cancellationToken);
    }
}

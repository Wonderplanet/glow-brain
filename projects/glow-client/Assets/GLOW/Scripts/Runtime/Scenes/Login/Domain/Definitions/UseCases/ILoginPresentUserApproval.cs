using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Login.Domain.UseCase;

namespace GLOW.Scenes.Login.Domain.UseCases
{
    public interface ILoginPresentUserApproval
    {
        UniTask<bool> PresentUserWithAgreementScreenAndCheckResult(CancellationToken cancellationToken, GameVersionModel gameVersionModel);
        UniTask<bool> PresentUserWithAgreementModuleScreenAndCheckResult(CancellationToken cancellationToken, AgreementUrl agreementUrl);
        UniTask<bool> PresentUserWithMstDataDownloadScreenAndCheckResult(CancellationToken cancellationToken, DownloadMetricsUseCaseModel downloadMetricsUseCaseModel);
        UniTask<bool> PresentUserWithAssetBundleDownloadScreenAndCheckResult(CancellationToken cancellationToken, DownloadMetricsUseCaseModel downloadMetricsUseCaseModel);
        UniTask PresentUserWithFreeSpaceError(CancellationToken cancellationToken);
        UniTask<bool> PresentUserWithAssetBundleRetryableDownload(CancellationToken cancellationToken);
    }
}

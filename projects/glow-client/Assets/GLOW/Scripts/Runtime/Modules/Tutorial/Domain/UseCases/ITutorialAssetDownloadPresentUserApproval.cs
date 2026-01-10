using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Modules.Tutorial.Domain.UseCases
{
    public interface ITutorialAssetDownloadPresentUserApproval
    {
        UniTask<bool> PresentUserWithAssetBundleDownloadScreenAndCheckResult(CancellationToken cancellationToken, AssetDownloadSize downloadSize, FreeSpaceSize freeSpaceSize);
        UniTask PresentUserWithFreeSpaceError(CancellationToken cancellationToken);
        UniTask<bool> PresentUserWithAssetBundleRetryableDownload(CancellationToken cancellationToken);
    }
}

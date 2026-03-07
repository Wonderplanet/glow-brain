using System;
using GLOW.Core.Domain.Models;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Modules.Tutorial.Domain.AssetDownloader
{
    public interface ITutorialAssetDownloader
    {
        void DoBackgroundAssetDownload(
                    Action onAssetDownloadConfirmed,
                    Action onAssetDownloadRefused,
                    GameVersionModel gameVersionModel);
        void CancelBackgroundAssetDownload();
        void SetProgressUpdateAction(Action<DownloadProgress> updateProgressAction);
        void SetPresentUserApproval(ITutorialAssetDownloadPresentUserApproval presentUserApproval);
        DownloadProgress GetDownloadProgress();
        bool IsStartBackgroundDownload();
    }
}

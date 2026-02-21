using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.AssetDownloadNotice.Presentation.ViewModels
{
    public record AssetDownloadNoticeViewModel(AssetDownloadSize DownloadSize, FreeSpaceSize FreeSpaceSize);
}

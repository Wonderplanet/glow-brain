using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Login.Domain.UseCase
{
    public record DownloadMetricsUseCaseModel(AssetDownloadSize TotalBytes, FreeSpaceSize FreeSpaceBytes)
    {
        public AssetDownloadSize TotalBytes { get; } = TotalBytes;
        public FreeSpaceSize FreeSpaceBytes { get; } = FreeSpaceBytes;
    }
}

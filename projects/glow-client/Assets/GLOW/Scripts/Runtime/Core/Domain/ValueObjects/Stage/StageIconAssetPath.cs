using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects.Stage
{
    public record StageIconAssetPath(string Value)
    {
        public static StageIconAssetPath Empty { get; } = new StageIconAssetPath(string.Empty);
        public static StageIconAssetPath FromAssetKey(StageAssetKey assetKey) => new StageIconAssetPath(ZString.Format("stage_icon_{0}", assetKey.Value));
    };
}

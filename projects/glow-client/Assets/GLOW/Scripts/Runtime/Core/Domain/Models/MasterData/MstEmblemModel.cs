using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstEmblemModel(
        MasterDataId Id,
        MasterDataId MstSeriesId,
        EmblemType EmblemType,
        EmblemAssetKey AssetKey,
        EmblemName Name,
        EmblemDescription Description)
    {
        public static MstEmblemModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            EmblemType.Event,
            EmblemAssetKey.Empty,
            EmblemName.Empty,
            EmblemDescription.Empty
        );
    }
}

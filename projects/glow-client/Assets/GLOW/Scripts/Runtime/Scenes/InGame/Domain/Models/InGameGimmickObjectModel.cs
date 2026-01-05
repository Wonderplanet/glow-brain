using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record InGameGimmickObjectModel(
        FieldObjectId Id,
        AutoPlayerSequenceElementId AutoPlayerSequenceElementId,
        InGameGimmickObjectAssetKey AssetKey,
        OutpostCoordV2 Pos,
        KomaModel LocatedKoma, // 現在いるコマのKomaModel
        NeedsRemovalFlag IsNeedsRemoval)
    {
        public static InGameGimmickObjectModel Empty { get; } = new (
            FieldObjectId.Empty,
            AutoPlayerSequenceElementId.Empty,
            InGameGimmickObjectAssetKey.Empty,
            OutpostCoordV2.Empty,
            KomaModel.Empty,
            NeedsRemovalFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

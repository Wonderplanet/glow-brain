using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Domain.Models
{
    public record MstKomaLineModel(
        float Height,
        KomaSetTypeAssetPath KomaSetTypeAssetPath,
        IReadOnlyList<MstKomaModel> KomaList)
    {
        public static MstKomaLineModel Empty { get; } = new(
            0f,
            KomaSetTypeAssetPath.Empty,
            new List<MstKomaModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

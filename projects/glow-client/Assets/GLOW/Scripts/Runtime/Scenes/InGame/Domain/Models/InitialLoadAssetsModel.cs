using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record InitialLoadAssetsModel(
        IReadOnlyList<UnitAssetKey> UnitAssetKeys,
        IReadOnlyList<KomaEffectAssetKey> KomaEffectAssetKeys,
        IReadOnlyList<KomaBackgroundAssetKey> KomaBackgroundAssetKeys,
        IReadOnlyList<MangaAnimationAssetKey> MangaAnimationAssetKeys,
        IReadOnlyList<OutpostAssetKey> OutpostAssetKeys,
        IReadOnlyList<InGameGimmickObjectAssetKey> GimmickObjectAssetKeys,
        IReadOnlyList<DefenseTargetAssetKey> DefenseTargetAssetKeys,
        IReadOnlyList<BGMAssetKey> BGMAssetKeys)
    {
        public static InitialLoadAssetsModel Empty { get; } = new(
            new List<UnitAssetKey>(),
            new List<KomaEffectAssetKey>(),
            new List<KomaBackgroundAssetKey>(),
            new List<MangaAnimationAssetKey>(),
            new List<OutpostAssetKey>(),
            new List<InGameGimmickObjectAssetKey>(),
            new List<DefenseTargetAssetKey>(),
            new List<BGMAssetKey>());
    }
}
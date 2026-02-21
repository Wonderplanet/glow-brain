using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Domain.Models
{
    public record MstKomaModel(
        KomaId KomaId,
        KomaBackgroundAssetKey BackgroundAssetKey,
        float Width,
        KomaBackgroundOffset BackgroundOffset,
        KomaEffectType KomaEffectType,
        KomaEffectParameter KomaEffectParameter1,
        KomaEffectParameter KomaEffectParameter2,
        KomaEffectTargetSide KomaEffectTargetSide,
        IReadOnlyList<CharacterColor> KomaEffectTargetColors,
        IReadOnlyList<CharacterUnitRoleType> KomaEffectTargetRoles)
    {
        public static MstKomaModel Empty { get; } = new(
            KomaId.Empty,
            KomaBackgroundAssetKey.Empty,
            0f,
            KomaBackgroundOffset.Empty,
            KomaEffectType.None,
            KomaEffectParameter.Empty,
            KomaEffectParameter.Empty,
            KomaEffectTargetSide.All,
            Array.Empty<CharacterColor>(),
            Array.Empty<CharacterUnitRoleType>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

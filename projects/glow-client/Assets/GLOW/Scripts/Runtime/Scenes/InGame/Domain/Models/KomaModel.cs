using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using WonderPlanet.UnityStandard.Extension;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record KomaModel(
        KomaId Id,
        StateEffectSourceId StateEffectSourceId,
        IReadOnlyList<IKomaEffectModel> KomaEffects)
    {
        public static KomaModel Empty { get; } = new (
            KomaId.Empty,
            StateEffectSourceId.Empty,
            Array.Empty<IKomaEffectModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsNormalKoma()
        {
            return KomaEffects.All(komaEffect => !komaEffect.IsEffective());
        }

        public bool IsDarknessKoma()
        {
            // 暗闇コマにいるかどうか
            var darknessKoma =  KomaEffects.Find(komaEffect => komaEffect.EffectType == KomaEffectType.Darkness);
            if (darknessKoma == null)
            {
                return false;
            }

            var darknessKomaEffect = darknessKoma as DarknessKomaEffectModel;
            if (darknessKomaEffect == null)
            {
                return false;
            }

            // 暗闇コマが晴れているかどうか
            return darknessKomaEffect.IsEffective();
        }

        public bool ExistsKomaEffects()
        {
            return KomaEffects.Count > 0;
        }

        public bool ExistsKomaEffectsAlwaysActive()
        {
            return KomaEffects.Any(effect => effect.IsAlwaysActive());
        }

        public bool CanSelectAsOutpostWeaponTarget()
        {
            return KomaEffects.All(effect => effect.CanSelectAsOutpostWeaponTarget());
        }

        public bool CanSelectAsSpecialUnitSummonTarget()
        {
            return KomaEffects.All(effect => effect.CanSelectAsSpecialUnitSummonTarget());
        }
    }
}

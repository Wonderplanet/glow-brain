using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record DarknessKomaClearedCommonConditionModel(KomaNo KomaNo) : ICommonConditionModel
    {
        public InGameCommonConditionType ConditionType => InGameCommonConditionType.DarknessKomaCleared;

        public bool MeetsCondition(ICommonConditionContext context)
        {
            var mstKoma = context.MstPage.GetKoma(KomaNo);
            if (mstKoma.IsEmpty()) return false;

            var komaDictionary = context.KomaDictionary;
            if (!komaDictionary.ContainsKey(mstKoma.KomaId)) return false;

            var koma = komaDictionary[mstKoma.KomaId];

            var darknessKomaEffectModels = koma.KomaEffects
                .Where(komaEffect => komaEffect.EffectType == KomaEffectType.Darkness)
                .Select(komaEffect => komaEffect as DarknessKomaEffectModel)
                .Where((komaEffect => komaEffect != null))
                .ToList();

            return darknessKomaEffectModels.Any(komaEffect => komaEffect.Cleared);
        }
    }
}

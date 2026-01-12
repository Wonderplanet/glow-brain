using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    /// <summary> Pvpの対戦相手用。プレイヤー用のものはInGameUnitEncyclopediaEffectProvider.csとなる </summary>
    public class InGamePvpOpponentUnitEncyclopediaEffectProvider : IInGameUnitEncyclopediaEffectProvider
    {
        [Inject] IPvpSelectedOpponentStatusCacheRepository PvpSelectedOpponentStatusCacheRepository { get; }
        [Inject] IMstUnitEncyclopediaEffectDataRepository MstUnitEncyclopediaEffectDataRepository { get; }

        [Inject]
        void OnInject()
        {
            var mstEffects =
                MstUnitEncyclopediaEffectDataRepository.GetUnitEncyclopediaEffects();

            var opponentPvpStatus = PvpSelectedOpponentStatusCacheRepository.GetOpponentStatus();
            var opponentHp = UnitEncyclopediaEffectValue.Empty;
            var opponentAttackPower = UnitEncyclopediaEffectValue.Empty;
            var opponentHeal = UnitEncyclopediaEffectValue.Empty;
            if (!opponentPvpStatus.IsEmpty())
            {
                foreach (var effect in opponentPvpStatus.UsrEncyclopediaEffects)
                {
                    if (effect.IsEmpty() || effect.MstEncyclopediaEffectId.IsEmpty()) continue;
                    var mstEffect = mstEffects.Find(mstEffect => mstEffect.Id == effect.MstEncyclopediaEffectId);
                    switch (mstEffect.EffectType)
                    {
                        case UnitEncyclopediaEffectType.Hp:
                            opponentHp += mstEffect.Value;
                            break;
                        case UnitEncyclopediaEffectType.AttackPower:
                            opponentAttackPower += mstEffect.Value;
                            break;
                        case UnitEncyclopediaEffectType.Heal:
                            opponentHeal += mstEffect.Value;
                            break;
                    }
                }
            }

            _opponentEncyclopediaEffectModel = new InGameUnitEncyclopediaEffectModel(
                opponentHp.ToPercentageM() + PercentageM.Hundred,
                opponentAttackPower.ToPercentageM() + PercentageM.Hundred,
                opponentHeal.ToPercentageM() + PercentageM.Hundred);
        }

        InGameUnitEncyclopediaEffectModel _opponentEncyclopediaEffectModel;

        public PercentageM GetHpEffectPercentage()
        {
            return _opponentEncyclopediaEffectModel.HpEffectRate;
        }

        public PercentageM GetAttackPowerEffectPercentage()
        {
            return _opponentEncyclopediaEffectModel.AttackPowerEffectRate;
        }

        public PercentageM GetHealEffectPercentage()
        {
            return _opponentEncyclopediaEffectModel.HealEffectRate;
        }
    }
}

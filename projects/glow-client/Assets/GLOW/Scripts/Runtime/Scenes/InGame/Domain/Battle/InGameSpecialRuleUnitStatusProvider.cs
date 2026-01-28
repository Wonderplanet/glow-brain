using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.PartyFormation.Domain.Evaluator;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class InGameSpecialRuleUnitStatusProvider : IInGameSpecialRuleUnitStatusProvider
    {
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IInGameSpecialRuleUnitStatusEvaluator InGameSpecialRuleUnitStatusEvaluator { get; }

        public SpecialRuleUnitStatusParameterModel
            GetSpecialRuleUnitStatus(
                MasterDataId mstUnitId,
                IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels)
        {
            var mstCharacterModel = MstCharacterDataRepository.GetCharacter(mstUnitId);
            return GetSpecialRuleUnitStatus(
                mstCharacterModel,
                specialRuleUnitStatusModels);
        }

        public SpecialRuleUnitStatusParameterModel
            GetSpecialRuleUnitStatus(
                MstCharacterModel mstCharacter,
                IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels)
        {
            var hpPercentageM = PercentageM.Hundred;
            var attackPercentageM = PercentageM.Hundred;
            var specialAttackCoolTime = TickCount.Empty;
            var summonCoolTime = TickCount.Empty;

            foreach (var specialRule in specialRuleUnitStatusModels)
            {
                if (!InGameSpecialRuleUnitStatusEvaluator.EvaluateTarget(
                        mstCharacter,
                        specialRule)) continue;

                switch (specialRule.StatusParameterType)
                {
                    case InGameSpecialRuleUnitStatusParameterType.Hp:
                        hpPercentageM += specialRule.EffectValue.ToPercentageM();
                        break;
                    case InGameSpecialRuleUnitStatusParameterType.AttackPower:
                        attackPercentageM += specialRule.EffectValue.ToPercentageM();
                        break;
                    case InGameSpecialRuleUnitStatusParameterType.SpecialAttackCoolTime:
                        specialAttackCoolTime += specialRule.EffectValue.ToTickCount();
                        break;
                    case InGameSpecialRuleUnitStatusParameterType.SummonCoolTime:
                        summonCoolTime += specialRule.EffectValue.ToTickCount();
                        break;
                }
            }

            return new SpecialRuleUnitStatusParameterModel(
                hpPercentageM,
                attackPercentageM,
                specialAttackCoolTime,
                summonCoolTime);
        }
    }
}

using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PartyFormation.Domain.Evaluator;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class InGameSpecialRuleUnitStatusProvider : IInGameSpecialRuleUnitStatusProvider
    {
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IInGameSpecialRuleUnitStatusEvaluator InGameSpecialRuleUnitStatusEvaluator { get; }

        public (PercentageM specialRuleHpPercentageM, PercentageM specialRuleAttackPercentageM)
            GetSpecialRuleUnitStatus(
                MasterDataId mstUnitId,
                IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels)
        {
            var mstCharacterModel = MstCharacterDataRepository.GetCharacter(mstUnitId);

            var hpPercentageM = PercentageM.Hundred;
            var attackPercentageM = PercentageM.Hundred;

            foreach (var specialRule in specialRuleUnitStatusModels)
            {
                if (!InGameSpecialRuleUnitStatusEvaluator.EvaluateTarget(
                        mstCharacterModel,
                        specialRule)) continue;

                switch (specialRule.StatusParameterType)
                {
                    case InGameSpecialRuleUnitStatusParameterType.Hp:
                        hpPercentageM += specialRule.EffectValue.ToPercentageM();
                        break;
                    case InGameSpecialRuleUnitStatusParameterType.AttackPower:
                        attackPercentageM += specialRule.EffectValue.ToPercentageM();
                        break;
                }
            }

            return (hpPercentageM, attackPercentageM);
        }
    }
}

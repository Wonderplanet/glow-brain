using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Scenes.PartyFormation.Domain.Evaluator
{
    public class InGameSpecialRuleUnitStatusEvaluator : IInGameSpecialRuleUnitStatusEvaluator
    {
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }
        [Inject] IMstInGameSpecialRuleUnitStatusDataRepository MstInGameSpecialRuleUnitStatusDataRepository { get; }

        public InGameSpecialRuleUnitStatusTargetFlag EvaluateTarget(
            UserUnitModel userUnit,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels)
        {
            var mstCharacter = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);

            return EvaluateTarget(mstCharacter, specialRuleUnitStatusModels);
        }

        public InGameSpecialRuleUnitStatusTargetFlag EvaluateTarget(
            MstCharacterModel mstCharacter,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels)
        {
            foreach (var specialRuleUnitStatusModel in specialRuleUnitStatusModels)
            {
                var targetFlag = EvaluateTarget(mstCharacter, specialRuleUnitStatusModel);
                if (targetFlag == InGameSpecialRuleUnitStatusTargetFlag.True) return targetFlag;
            }

            return InGameSpecialRuleUnitStatusTargetFlag.False;
        }

        public InGameSpecialRuleUnitStatusTargetFlag EvaluateTarget(
            MstCharacterModel mstCharacter,
            MstInGameSpecialRuleUnitStatusModel specialRuleUnitStatusModel)
        {
            // スペシャルキャラは対象外
            if (mstCharacter.RoleType == CharacterUnitRoleType.Special)
            {
                return InGameSpecialRuleUnitStatusTargetFlag.False;
            }

            if (specialRuleUnitStatusModel.IsEmpty())
            {
                return InGameSpecialRuleUnitStatusTargetFlag.False;
            }

            var isTarget = specialRuleUnitStatusModel.TargetType switch
            {
                InGameSpecialRuleUnitStatusTargetType.All => true,

                InGameSpecialRuleUnitStatusTargetType.Unit => specialRuleUnitStatusModel.TargetValue.ToMasterDataId() ==
                                                               mstCharacter.Id,

                InGameSpecialRuleUnitStatusTargetType.CharacterUnitRoleType =>
                    specialRuleUnitStatusModel.TargetValue.ToCharacterUnitRoleType() == mstCharacter.RoleType,

                InGameSpecialRuleUnitStatusTargetType.CharacterColor =>
                    specialRuleUnitStatusModel.TargetValue.ToCharacterColor() == mstCharacter.Color,

                _ => false
            };

            return isTarget
                ? InGameSpecialRuleUnitStatusTargetFlag.True
                : InGameSpecialRuleUnitStatusTargetFlag.False;
        }


        public InGameSpecialRuleUnitStatusFlag HasSpecialRuleUnitStatus(
            MasterDataId contentId,
            InGameContentType inGameContentType)
        {
            if (contentId.IsEmpty())
            {
                return InGameSpecialRuleUnitStatusFlag.False;
            }

            var specialRules = MstInGameSpecialRuleDataRepository.GetInGameSpecialRuleModels(
                contentId,
                inGameContentType);

            var groupIds = specialRules
                .Where(rule => rule.RuleType == RuleType.UnitStatus)
                .Select(rule => rule.RuleValue.ToMasterDataId())
                .ToList();

            var unitStatusModels = MstInGameSpecialRuleUnitStatusDataRepository
                .GetInGameSpecialRuleUnitStatusModels(groupIds);

            return unitStatusModels.IsEmpty()
                ? InGameSpecialRuleUnitStatusFlag.False
                : InGameSpecialRuleUnitStatusFlag.True;
        }
    }
}


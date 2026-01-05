using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Unit;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Core.Domain.Calculator
{
    public class TotalPartyStatusCalculator
    {
        [Inject] IInGameEventBonusUnitEffectProvider InGameEventBonusUnitEffectProvider { get; }
        [Inject] IInGameSpecialRuleUnitStatusProvider InGameSpecialRuleUnitStatusProvider { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }

        public TotalPartyStatus CalculateTotalPartyStatus(
            IReadOnlyList<(MasterDataId, UnitCalculateStatusModel)> calculatedStatusList,
            InGameUnitEncyclopediaEffectModel unitEncyclopediaEffectModel,
            EventBonusGroupId eventBonusGroupId,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels,
            InGameContentType inGameType)
        {
            TotalPartyStatus totalStatus = TotalPartyStatus.Empty;

            foreach (var calculatedStatus in calculatedStatusList)
            {
                var statusWithBonus = CalculateStatus(
                    calculatedStatus.Item2,
                    calculatedStatus.Item1,
                    unitEncyclopediaEffectModel,
                    eventBonusGroupId,
                    specialRuleUnitStatusModels,
                    inGameType);
                totalStatus += statusWithBonus.HP;
                totalStatus += statusWithBonus.AttackPower;
            }

            return totalStatus;
        }

        UnitCalculateStatusModel CalculateStatus(
            UnitCalculateStatusModel calculatedStatus,
            MasterDataId unitId,
            InGameUnitEncyclopediaEffectModel unitEncyclopediaEffectModel,
            EventBonusGroupId eventBonusGroupId,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels,
            InGameContentType inGameType)
        {
            var unitModel = MstCharacterDataRepository.GetCharacter(unitId);
            // スペシャルキャラは効果対象外
            if (unitModel.RoleType == CharacterUnitRoleType.Special)
            {
                return UnitCalculateStatusModel.Empty;
            }

            var eventBonus = GetEventBonus(
                unitId,
                inGameType,
                eventBonusGroupId);
            var hpEncyclopediaEffect = unitEncyclopediaEffectModel.HpEffectRate;
            var attackPowerEncyclopediaEffect = unitEncyclopediaEffectModel.AttackPowerEffectRate;
            var specialRuleUnitStatusParameter = InGameSpecialRuleUnitStatusProvider
                .GetSpecialRuleUnitStatus(
                    unitId,
                    specialRuleUnitStatusModels);

            // HP計算は浮動小数点数で行い、最後に切り上げ
            var hpFloat = calculatedStatus.HP.Value *
                (float)hpEncyclopediaEffect.Value / 100f *
                (float)eventBonus.Value / 100f *
                (float)specialRuleUnitStatusParameter.specialRuleHpPercentageM.Value / 100f;

            var hp = new HP((int)Math.Ceiling(hpFloat));

            var attackPower = calculatedStatus.AttackPower *
                              attackPowerEncyclopediaEffect *
                              eventBonus *
                              specialRuleUnitStatusParameter.specialRuleAttackPercentageM.ToRate();

            return new UnitCalculateStatusModel(hp, attackPower);
        }

        PercentageM GetEventBonus(
            MasterDataId characterId,
            InGameContentType inGameType,
            EventBonusGroupId eventBonusGroupId)
        {
            if (inGameType == InGameContentType.AdventBattle)
            {
                return InGameEventBonusUnitEffectProvider.GetUnitEventBonusPercentageM(
                    characterId,
                    eventBonusGroupId);
            }
            else
            {
                return PercentageM.Hundred;
            }
        }
    }
}

using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Models.LogModel;
using Zenject;

namespace GLOW.Scenes.AdventBattle.Domain.Factory
{
    public class PartyStatusModelFactory : IPartyStatusModelFactory
    {
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject] IInGameUnitStatusCalculator InGameUnitStatusCalculator { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject(Id = InGameUnitEncyclopediaBindIds.Player)]
        IInGameUnitEncyclopediaEffectProvider UnitEncyclopediaEffectProvider { get; }
        [Inject(Id = InGameUnitEncyclopediaBindIds.PvpOpponent)]
        IInGameUnitEncyclopediaEffectProvider PvpOpponentUnitEncyclopediaEffectProvider { get; }

        public PartyStatusModel CreatePartyStatusModel(
            UserUnitModel userUnitModel,
            InGameType inGameType,
            MasterDataId questId,
            EventBonusGroupId eventBonusGroupId,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels)
        {
            var mstUnitModel = MstCharacterDataRepository.GetCharacter(userUnitModel.MstUnitId);
            var calcStatusModel = UnitStatusCalculateHelper
                .Calculate(mstUnitModel, userUnitModel.Level, userUnitModel.Rank, userUnitModel.Grade);
            var calcStatusWithBonusModel = InGameUnitStatusCalculator.CalculateStatus(
                calcStatusModel,
                mstUnitModel.Id,
                UnitEncyclopediaEffectProvider,
                inGameType,
                questId,
                eventBonusGroupId,
                specialRuleUnitStatusModels);

            var specialAttack = mstUnitModel.GetSpecialAttack(userUnitModel.Grade);

            var abilityIds = mstUnitModel
                .GetUnlockedMstUnitAbilityModels(userUnitModel.Rank)
                .Select(model => model.Id)
                .ToList();

            return new PartyStatusModel(
                userUnitModel.UsrUnitId,
                userUnitModel.MstUnitId,
                mstUnitModel.Color,
                mstUnitModel.RoleType,
                calcStatusWithBonusModel.HP,
                calcStatusWithBonusModel.AttackPower.ToCeiling(),
                mstUnitModel.UnitMoveSpeed,
                mstUnitModel.SummonCost.ToSummonCost(),
                mstUnitModel.SummonCoolTime,
                mstUnitModel.DamageKnockBackCount,
                specialAttack.MstAttackId,
                mstUnitModel.NormalMstAttackModel.AttackData.AttackDelay,
                mstUnitModel.NormalMstAttackModel.AttackData.BaseData.AttackInterval,
                abilityIds);
        }

        public PartyStatusModel CreatePartyStatusModel(
            PvpUnitModel pvpUnitModel,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels)
        {
            var mstUnitModel = MstCharacterDataRepository.GetCharacter(pvpUnitModel.MstUnitId);
            var calcStatusModel = UnitStatusCalculateHelper.Calculate(
                mstUnitModel,
                pvpUnitModel.UnitLevel,
                pvpUnitModel.UnitRank,
                pvpUnitModel.UnitGrade);

            var calcStatusWithBonusModel = InGameUnitStatusCalculator.CalculateStatus(
                calcStatusModel,
                mstUnitModel.Id,
                PvpOpponentUnitEncyclopediaEffectProvider,
                InGameType.Pvp,
                MasterDataId.Empty,
                EventBonusGroupId.Empty,
                specialRuleUnitStatusModels);

            var specialAttack = mstUnitModel.GetSpecialAttack(pvpUnitModel.UnitGrade);

            var abilityIds = mstUnitModel
                .GetUnlockedMstUnitAbilityModels(pvpUnitModel.UnitRank)
                .Select(model => model.Id)
                .ToList();

            return new PartyStatusModel(
                UserDataId.Empty,
                pvpUnitModel.MstUnitId,
                mstUnitModel.Color,
                mstUnitModel.RoleType,
                calcStatusWithBonusModel.HP,
                calcStatusWithBonusModel.AttackPower.ToCeiling(),
                mstUnitModel.UnitMoveSpeed,
                mstUnitModel.SummonCost.ToSummonCost(),
                mstUnitModel.SummonCoolTime,
                mstUnitModel.DamageKnockBackCount,
                specialAttack.MstAttackId,
                mstUnitModel.NormalMstAttackModel.AttackData.AttackDelay,
                mstUnitModel.NormalMstAttackModel.AttackData.BaseData.AttackInterval,
                abilityIds);
        }
    }
}

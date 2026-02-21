using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Models.Unit;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.PartyFormation.Domain.Evaluator;
using GLOW.Scenes.PvpTop.Domain.Model;
using Zenject;

namespace GLOW.Scenes.PvpTop.Domain.ModelFactories
{
    public class PvpTopOpponentModelFactory : IPvpTopOpponentModelFactory
    {
        [Inject] IMstEmblemRepository MstEmblemRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IPvpUserRankStatusFactory PvpUserRankStatusFactory { get; }
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject] IMstUnitEncyclopediaEffectDataRepository MstUnitEncyclopediaEffectDataRepository { get; }
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }
        [Inject] IMstInGameSpecialRuleUnitStatusDataRepository MstInGameSpecialRuleUnitStatusDataRepository { get; }
        [Inject] IInGameSpecialRuleUnitStatusEvaluator InGameSpecialRuleUnitStatusEvaluator { get; }
        [Inject] TotalPartyStatusCalculator TotalPartyStatusCalculator { get; }

        public IReadOnlyList<PvpTopOpponentModel> Create(
            IReadOnlyList<OpponentSelectStatusModel> opponentSelectStatusModels,
            MasterDataId mstPvpId)
        {
            return opponentSelectStatusModels
                .Select(x => CreatePvpTopOpponentModel(x, mstPvpId))
                .OrderByDescending(o => o.Point.Value)
                .ToList();
        }

        PvpTopOpponentModel CreatePvpTopOpponentModel(
            OpponentSelectStatusModel opponentSelectStatusModel,
            MasterDataId mstPvpId)
        {
            var mstCharacterModel = opponentSelectStatusModel.MstUnitId.IsEmpty() ?
                MstCharacterModel.Empty :
                MstCharacterDataRepository.GetCharacter(opponentSelectStatusModel.MstUnitId);

            var emblemAssetPath = EmblemIconAssetPath.Empty;
            if (!opponentSelectStatusModel.MstEmblemId.IsEmpty())
            {
                // 空じゃない場合のみ設定する
                var emblemModel = MstEmblemRepository.GetMstEmblemFirstOrDefault(opponentSelectStatusModel.MstEmblemId);
                emblemAssetPath = EmblemIconAssetPath.FromAssetKey(emblemModel.AssetKey);
            }

            var unitIconAssetPath = mstCharacterModel.AssetKey.IsEmpty() ?
                CharacterIconAssetPath.Empty :
                CharacterIconAssetPath.FromAssetKey(mstCharacterModel.AssetKey);

            var partyUnits = opponentSelectStatusModel.OpponentSelectStatus.PvpUnits
                .Select(pvpUnitModel =>
                {
                    var partyUnit = pvpUnitModel.IsEmpty() ?
                        MstCharacterModel.Empty :
                        MstCharacterDataRepository.GetCharacter(pvpUnitModel.MstUnitId);

                    if (partyUnit.IsEmpty())
                    {
                        return PvpTopOpponentPartyUnitModel.Empty;
                    }

                    return new PvpTopOpponentPartyUnitModel(
                        CharacterIconAssetPath.FromAssetKey(partyUnit.AssetKey),
                        partyUnit.RoleType,
                        partyUnit.Color,
                        partyUnit.Rarity,
                        pvpUnitModel.UnitLevel,
                        pvpUnitModel.UnitGrade
                    );
                })
                .ToList();

            var specialRuleUnitStatusModels = GetInGameSpecialRuleUnitStatusModels(mstPvpId, InGameContentType.Pvp);

            var unitStatusesList = opponentSelectStatusModel.OpponentSelectStatus.PvpUnits
                .Select(unit =>
                {
                    var mstUnit = MstCharacterDataRepository.GetCharacter(unit.MstUnitId);
                    var unitStatuses = UnitStatusCalculateHelper.Calculate(
                        mstUnit,
                        unit.UnitLevel,
                        unit.UnitRank,
                        unit.UnitGrade);
                    return (unit.MstUnitId, unitStatuses);
                })
                .ToList();

            var totalPartyStatusUpperArrowFlag = opponentSelectStatusModel.OpponentSelectStatus.PvpUnits
                .Any(unit =>
                {
                    var mstUnit = MstCharacterDataRepository.GetCharacter(unit.MstUnitId);
                    return InGameSpecialRuleUnitStatusEvaluator.EvaluateTarget(mstUnit, specialRuleUnitStatusModels);
                })
                ? TotalPartyStatusUpperArrowFlag.True
                : TotalPartyStatusUpperArrowFlag.False;

            var totalPartyStatus = TotalPartyStatusCalculator.CalculateTotalPartyStatus(
                unitStatusesList,
                GetEncyclopediaEffectModel(opponentSelectStatusModel.OpponentSelectStatus),
                EventBonusGroupId.Empty,
                specialRuleUnitStatusModels,
                InGameContentType.Pvp);

            return new PvpTopOpponentModel(
                opponentSelectStatusModel.MyId,
                opponentSelectStatusModel.Name,
                unitIconAssetPath,
                emblemAssetPath,
                opponentSelectStatusModel.WinAddPoint,
                opponentSelectStatusModel.Score,
                PvpUserRankStatusFactory.Create(opponentSelectStatusModel.Score),
                partyUnits,
                totalPartyStatus,
                totalPartyStatusUpperArrowFlag
            );
        }

        InGameUnitEncyclopediaEffectModel GetEncyclopediaEffectModel(
            OpponentPvpStatusModel opponentPvpStatus)
        {
            var mstEffects =
                MstUnitEncyclopediaEffectDataRepository.GetUnitEncyclopediaEffects();

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

            return new InGameUnitEncyclopediaEffectModel(
                opponentHp.ToPercentageM() + PercentageM.Hundred,
                opponentAttackPower.ToPercentageM() + PercentageM.Hundred,
                opponentHeal.ToPercentageM() + PercentageM.Hundred);
        }

        IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> GetInGameSpecialRuleUnitStatusModels(
            MasterDataId mstStageId,
            InGameContentType inGameContentType)
        {
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels = new List<MstInGameSpecialRuleModel>();
            if (!mstStageId.IsEmpty())
            {
                mstInGameSpecialRuleModels = MstInGameSpecialRuleDataRepository.GetInGameSpecialRuleModels(
                    mstStageId,
                    inGameContentType);
            }

            var groupIds = mstInGameSpecialRuleModels
                .Where(rule => rule.RuleType == RuleType.UnitStatus)
                .Select(rule => rule.RuleValue.ToMasterDataId())
                .ToList();

            var specialRuleUnitStatusModels = MstInGameSpecialRuleUnitStatusDataRepository
                .GetInGameSpecialRuleUnitStatusModels(groupIds);

            return specialRuleUnitStatusModels;
        }
    }
}

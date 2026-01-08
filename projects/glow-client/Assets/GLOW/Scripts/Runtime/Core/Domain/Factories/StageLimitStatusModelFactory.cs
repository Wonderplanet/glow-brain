using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Extensions;
using GLOW.Scenes.Home.Domain.Models;
using Zenject;

namespace GLOW.Core.Domain.Factories
{
    public class StageLimitStatusModelFactory : IStageLimitStatusModelFactory
    {
        [Inject] IMstSeriesDataRepository MstSeriesDataRepository { get; }
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }

        public InGameSpecialRuleStatusModel CreateInvalidStageLimitStatusModel(
            MasterDataId specialRuleTargetMstId,
            InGameContentType specialRuleContentType,
            PartyName partyName,
            IReadOnlyList<MstCharacterModel> characterModels)
        {
            var stageLimitStatusModel = CreateStageLimitStatusModel(specialRuleTargetMstId, specialRuleContentType, partyName);
            var limitStatusList = stageLimitStatusModel.LimitStatus;

            var result = new List<StageLimitStatus>();

            // PartyUnitNum
            var partyUnitNumStatus = limitStatusList.FirstOrDefault(model => model.Status == StageLimitPartyStatus.PartyUnitNum, StageLimitStatus.Empty);
            if (!partyUnitNumStatus.IsEmpty())
            {
                if (partyUnitNumStatus.PartyUnitNum.Value < characterModels.Count)
                {
                    result.Add(partyUnitNumStatus);
                }
            }
            // PartyRarity
            var rarityStatus = limitStatusList.FirstOrDefault(model => model.Status == StageLimitPartyStatus.PartyRarity, StageLimitStatus.Empty);
            if (!rarityStatus.IsEmpty())
            {
                if (!rarityStatus.Rarities.IsEmpty() &&
                    characterModels.Any(m => !rarityStatus.Rarities.Contains(m.Rarity)))
                {
                    result.Add(rarityStatus);
                }
            }
            // PartySeries
            var seriesStatus = limitStatusList.FirstOrDefault(model => model.Status == StageLimitPartyStatus.PartySeries, StageLimitStatus.Empty);
            if (!seriesStatus.IsEmpty())
            {
                if (!seriesStatus.SeriesAssetKeys.IsEmpty() &&
                    characterModels.Any(m => !seriesStatus.SeriesAssetKeys.Contains(m.SeriesAssetKey)))
                {
                    result.Add(seriesStatus);
                }
            }
            // PartyAttackRangeType
            var attackRangeStatus = limitStatusList.FirstOrDefault(model => model.Status == StageLimitPartyStatus.PartyAttackRangeType, StageLimitStatus.Empty);
            if (!attackRangeStatus.IsEmpty())
            {
                if (!attackRangeStatus.UnitAttackRangeTypes.IsEmpty() &&
                    characterModels.Any(m => !attackRangeStatus.UnitAttackRangeTypes.Contains(m.AttackRangeType)))
                {
                    result.Add(attackRangeStatus);
                }
            }
            // PartyRoleType
            var roleTypeStatus = limitStatusList.FirstOrDefault(model => model.Status == StageLimitPartyStatus.PartyRoleType, StageLimitStatus.Empty);
            if (!roleTypeStatus.IsEmpty())
            {
                if (!roleTypeStatus.UnitRoleTypes.IsEmpty() &&
                    characterModels.Any(m => !roleTypeStatus.UnitRoleTypes.Contains(m.RoleType)))
                {
                    result.Add(roleTypeStatus);
                }
            }

            return new InGameSpecialRuleStatusModel(partyName, result);
        }

        public InGameSpecialRuleStatusModel CreateStageLimitStatusModel(
            MasterDataId specialRuleTargetMstId,
            InGameContentType specialRuleContentType,
            PartyName partyName)
        {
            var inGameSpecialRules = MstInGameSpecialRuleDataRepository.GetInGameSpecialRuleModels(specialRuleTargetMstId, specialRuleContentType);
            if (inGameSpecialRules.IsEmpty())
            {
                return new InGameSpecialRuleStatusModel(partyName, new List<StageLimitStatus>());
            }

            // 編成条件不足チェック
            var result = new List<StageLimitStatus>();

            // PartyUnitNum
            var mstStageEventRuleModel = inGameSpecialRules.Find(model => model.RuleType == RuleType.PartyUnitNum);
            if (null != mstStageEventRuleModel && !mstStageEventRuleModel.IsEmpty())
            {
                var unitAmount = mstStageEventRuleModel.RuleValue.ToUnitAmount();
                result.Add(StageLimitStatus.Empty with
                {
                    Status = StageLimitPartyStatus.PartyUnitNum,
                    PartyUnitNum = new PartyUnitNum(unitAmount)
                });
            }
            // PartyRarity
            var rarities = inGameSpecialRules
                .Where(model => model.RuleType == RuleType.PartyRarity)
                .Select(model => model.RuleValue.ToRarity())
                .ToList();
            if (!rarities.IsEmpty())
            {
                result.Add(StageLimitStatus.Empty with
                {
                    Status = StageLimitPartyStatus.PartyRarity,
                    Rarities = rarities
                });
            }
            // PartySeries
            var mstSeries = inGameSpecialRules
                .Where(model => model.RuleType == RuleType.PartySeries)
                .Select(model => model.RuleValue.ToSeriesId())
                .Select(MstSeriesDataRepository.GetMstSeriesModel)
                .ToList();
            if (!mstSeries.IsEmpty())
            {
                result.Add(StageLimitStatus.Empty with
                {
                    Status = StageLimitPartyStatus.PartySeries,
                    SeriesLogImageAssetPathList = mstSeries
                        .Select(mst => new SeriesLogoImagePath(SeriesAssetPath.GetSeriesLogoPath(mst.SeriesAssetKey.Value)))
                        .ToList(),
                    SeriesAssetKeys = mstSeries
                        .Select(m => m.SeriesAssetKey)
                        .ToList()
                });
            }
            // PartyAttackRangeType
            var attackRangeTypes = inGameSpecialRules
                .Where(model => model.RuleType == RuleType.PartyAttackRangeType)
                .Select(model => model.RuleValue.ToAttackRangeType())
                .ToList();
            if (!attackRangeTypes.IsEmpty())
            {
                result.Add(StageLimitStatus.Empty with
                {
                    Status = StageLimitPartyStatus.PartyAttackRangeType,
                    UnitAttackRangeTypes = attackRangeTypes
                });
            }
            // PartyRoleType
            var roleTypes = inGameSpecialRules
                .Where(model => model.RuleType == RuleType.PartyRoleType)
                .Select(model => model.RuleValue.ToUnitRoleType())
                .ToList();
            if (!roleTypes.IsEmpty())
            {
                result.Add(StageLimitStatus.Empty with
                {
                    Status = StageLimitPartyStatus.PartyRoleType,
                    UnitRoleTypes = roleTypes
                });
            }

            return new InGameSpecialRuleStatusModel(partyName, result);
        }
    }
}

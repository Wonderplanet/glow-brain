using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Misc;
using Zenject;

namespace GLOW.Scenes.PartyFormation.Domain.UseCases
{
    public class UpdatePartyUnitListUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IUnitSortFilterCacheRepository UnitSortFilterCacheRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstSeriesDataRepository MstSeriesDataRepository { get; }
        [Inject] IMstUnitLevelUpRepository MstUnitLevelUpRepository { get; }
        [Inject] IUnitListFilterAndSort UnitListFilterAndSort { get; }
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }
        [Inject] IMstInGameSpecialRuleUnitStatusDataRepository MstInGameSpecialRuleUnitStatusDataRepository { get; }

        public void UpdateUnitList(
            PartyNo partyNo,
            MasterDataId specialTargetMstId,
            InGameContentType specialRuleContentType,
            UnitSortFilterCacheType cacheType)
        {
            PartyCacheRepository.SetSelectPartyNo(partyNo);

            // ソートとフィルターを適用
            var filterCategoryModel = UnitSortFilterCacheRepository.GetModel(cacheType);
            var filteredUnits = GetFilteredUnitList(filterCategoryModel, specialTargetMstId, specialRuleContentType);

            var currentParty = PartyCacheRepository.GetCacheParty(partyNo);
            var assignedPartyUnitIds = currentParty.GetUnitList();

            // 編成中は優先で前に
            var sortAndFilteredUnitIds = filteredUnits
                .OrderByDescending(unit => assignedPartyUnitIds.Contains(unit.UsrUnitId))
                .Select(model => model.UsrUnitId)
                .ToList();

            PartyCacheRepository.SetUnitList(partyNo, sortAndFilteredUnitIds);
        }

        List<UserUnitModel> GetFilteredUnitList(UnitSortFilterCategoryModel sortFilterCategoryModel,
            MasterDataId specialRuleTargetMstId,
            InGameContentType specialRuleContentType)
        {
            var userUnits = GameRepository.GetGameFetchOther().UserUnitModels;
            var userMstUnits = userUnits
                .Select(unit => MstCharacterDataRepository.GetCharacter(unit.MstUnitId))
                .ToList();

            var mstSeriesModels = MstSeriesDataRepository.GetMstSeriesModels();

            var partyBonusUnits = PartyCacheRepository.GetBonusUnits();

            var mstUnitLevelUpList = MstUnitLevelUpRepository.GetUnitLevelUpList();

            var achievedSpecialRuleUnitIds = GetAchievedSpecialRuleUnitIds(userUnits, specialRuleTargetMstId, specialRuleContentType);
            var notAchieveSpecialRuleUnitIds = userUnits
                .Select(unit => unit.UsrUnitId)
                .Except(achievedSpecialRuleUnitIds)
                .ToList();

            var specialRuleModels = MstInGameSpecialRuleDataRepository
                .GetInGameSpecialRuleModels(
                    specialRuleTargetMstId,
                    specialRuleContentType);
            var groupIdList = specialRuleModels
                .Where(m => m.RuleType == RuleType.UnitStatus)
                .Select(m => m.RuleValue.ToMasterDataId())
                .Distinct()
                .ToList();
            var unitStatusModels = MstInGameSpecialRuleUnitStatusDataRepository
                .GetInGameSpecialRuleUnitStatusModels(groupIdList);

            // 編成されているキャラいないキャラ関係なく混ぜた上でソートする
            var sortAndFilteredUnits = UnitListFilterAndSort.FilterAndSort(
                userUnits,
                userMstUnits,
                partyBonusUnits,
                sortFilterCategoryModel,
                mstSeriesModels,
                mstUnitLevelUpList,
                achievedSpecialRuleUnitIds,
                notAchieveSpecialRuleUnitIds,
                unitStatusModels).ToList();

            return sortAndFilteredUnits;
        }

        IReadOnlyList<UserDataId> GetAchievedSpecialRuleUnitIds(
            IReadOnlyList<UserUnitModel> userUnitModels,
            MasterDataId specialRuleTargetMstId,
            InGameContentType specialRuleContentType)
        {
            if (specialRuleTargetMstId.IsEmpty())
            {
                return new List<UserDataId>();
            }
            var mstInGameSpecialRuleModels = MstInGameSpecialRuleDataRepository.GetInGameSpecialRuleModels(
                specialRuleTargetMstId,
                specialRuleContentType);
            return userUnitModels
                .Where(unit =>
                {
                    var mstCharacter = MstCharacterDataRepository.GetCharacter(unit.MstUnitId);
                    return InGameSpecialRuleAchievingEvaluator.IsAchievedSpecialRule(mstCharacter, mstInGameSpecialRuleModels);
                })
                .Select(unit => unit.UsrUnitId)
                .ToList();
        }
    }
}

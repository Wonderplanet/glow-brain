using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Party;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.PartyFormation.Domain.Evaluator;
using GLOW.Scenes.PartyFormation.Domain.Models;
using GLOW.Scenes.UnitList.Domain.Constants;
using WonderPlanet.UnityStandard.Extension;
using Zenject;
using IReadOnlyListExtension = GLOW.Core.Extensions.IReadOnlyListExtension;

namespace GLOW.Scenes.PartyFormation.Domain.UseCases
{
    public class GetPartyFormationUnitListUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IUnitSortFilterCacheRepository UnitSortFilterCacheRepository { get; }
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }
        [Inject] IMstInGameSpecialRuleUnitStatusDataRepository MstInGameSpecialRuleUnitStatusDataRepository { get; }
        [Inject] IInGameSpecialRuleUnitStatusEvaluator InGameSpecialRuleUnitStatusEvaluator { get; }

        public PartyFormationUnitListModel GetUnitListModel(
            PartyNo currentPartyNo,
            UnitSortFilterCacheType cacheType,
            MasterDataId mstStageId,
            InGameContentType inGameContentType)
        {
            var currentParty = PartyCacheRepository.GetCacheParty(currentPartyNo);
            var assignedPartyUnitIds = currentParty.GetUnitList();

            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userUnits = PartyCacheRepository.GetUnitList(currentPartyNo);
            var filteredUnits = userUnits
                .Select(id => gameFetchOther.UserUnitModels.Find(model => model.UsrUnitId == id));

            var filterCategoryModel = UnitSortFilterCacheRepository.GetModel(cacheType);

            var partyBonusUnits = PartyCacheRepository.GetBonusUnits();

            bool isFullParty = assignedPartyUnitIds.All(id => !id.IsEmpty())
                               || IsPartyFormationLimitReached(mstStageId, inGameContentType, assignedPartyUnitIds);

            var specialRuleUnitStatusModels = GetInGameSpecialRuleUnitStatusModels(mstStageId, inGameContentType);

            var cellModels = filteredUnits
                .Select(unit => Translate(
                    unit,
                    assignedPartyUnitIds,
                    filterCategoryModel.SortType,
                    partyBonusUnits,
                    isFullParty,
                    specialRuleUnitStatusModels))
                .ToList();

            return new PartyFormationUnitListModel(
                cellModels,
                filterCategoryModel);
        }

        PartyFormationUnitListCellModel Translate(
            UserUnitModel userUnit,
            IReadOnlyList<UserDataId> assignedPartyUnitIds,
            UnitListSortType sortType,
            IReadOnlyList<PartyBonusUnitModel> partyBonusUnits,
            bool isFullParty,
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels)
        {
            var mstCharacter = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);
            var calculateStatus = UnitStatusCalculateHelper.Calculate(mstCharacter, userUnit.Level, userUnit.Rank, userUnit.Grade);
            var calculateStatusWithSpecialRule = UnitStatusCalculateHelper.CalculateStatusWithSpecialRule(
                calculateStatus,
                mstCharacter.Id,
                specialRuleUnitStatusModels);
            var characterIcon = CharacterIconModelFactory.CreateLSize(mstCharacter, userUnit, calculateStatusWithSpecialRule);
            var isAssigned = assignedPartyUnitIds.Any(unitId => unitId == userUnit.UsrUnitId);
            var eventBonus = partyBonusUnits
                .FirstOrDefault(unit => unit.MstUnitId == userUnit.MstUnitId)
                ?.BonusPercentage ?? EventBonusPercentage.Empty;
            var isSelectable = isAssigned || !isFullParty;

            var isSpecialRuleUnitStatusTarget = InGameSpecialRuleUnitStatusEvaluator.EvaluateTarget(
                userUnit,
                specialRuleUnitStatusModels);

            return new PartyFormationUnitListCellModel(
                userUnit.UsrUnitId,
                characterIcon,
                new PartyFormationAssignFlag(isAssigned),
                new PartyFormationUnitSelectableFlag(isSelectable),
                sortType,
                new NotificationBadge(false),
                eventBonus,
                isSpecialRuleUnitStatusTarget
                );
        }

        bool IsPartyFormationLimitReached(
            MasterDataId mstStageId,
            InGameContentType inGameContentType,
            IReadOnlyList<UserDataId> assignedPartyUnitIds)
        {
            if( mstStageId.IsEmpty()) return false;

            var specialRules = MstInGameSpecialRuleDataRepository
                .GetInGameSpecialRuleModels(mstStageId, inGameContentType);

            var partyUnitNumRules = specialRules
                .Where(rule => rule.RuleType == RuleType.PartyUnitNum)
                .ToList();
            if (partyUnitNumRules.IsEmpty()) return false;

            var partyUnitNum = partyUnitNumRules
                .Select(rule => rule.RuleValue.ToInt())
                .Min();

            var assignedPartyUnitNum = assignedPartyUnitIds
                .Count(unitId => !unitId.IsEmpty());

            return assignedPartyUnitNum >= partyUnitNum;
        }

        List<MstInGameSpecialRuleUnitStatusModel> GetInGameSpecialRuleUnitStatusModels(
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
                .GetInGameSpecialRuleUnitStatusModels(groupIds)
                .ToList();

            return specialRuleUnitStatusModels;
        }
    }
}

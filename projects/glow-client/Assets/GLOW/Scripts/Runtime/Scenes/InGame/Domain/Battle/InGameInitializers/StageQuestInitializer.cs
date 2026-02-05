using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.PvpTop.Domain.Resolver;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public class StageQuestInitializer : IStageQuestInitializer
    {
        [Inject] ISelectedStageEvaluator SelectedStageEvaluator { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }
        [Inject] IMstInGameSpecialRuleUnitStatusDataRepository MstInGameSpecialRuleUnitStatusDataRepository { get; }
        [Inject] IMstStageEndConditionDataRepository MstStageEndConditionDataRepository { get; }
        [Inject] IMstCurrentPvpModelResolver MstCurrentPvpModelResolver { get; }

        public StageQuestInitializationResult Initialize()
        {
            SelectedStageModel selectedStage = SelectedStageEvaluator.GetSelectedStage();

            IMstInGameModel mstInGameModel;
            var mstStage = MstStageModel.Empty;
            var mstAdventBattle = MstAdventBattleModel.Empty;
            var mstQuest = MstQuestModel.Empty;

            switch (selectedStage.InGameType)
            {
                case InGameType.AdventBattle:
                    mstAdventBattle = MstAdventBattleDataRepository.GetMstAdventBattleModel(
                        selectedStage.SelectedMstAdventBattleId);
                    mstInGameModel = mstAdventBattle;
                    break;
                case InGameType.Pvp:
                    mstInGameModel = MstCurrentPvpModelResolver.CreateMstPvpBattleModel(
                        selectedStage.SelectedSysPvpSeasonId);
                    break;
                case InGameType.Normal:
                default:
                    mstStage = MstStageDataRepository.GetMstStage(selectedStage.SelectedStageId);
                    mstInGameModel = mstStage;
                    mstQuest = MstQuestDataRepository.GetMstQuestModelFirstOrDefault(mstStage.MstQuestId);
                    break;
            }

            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRules = new List<MstInGameSpecialRuleModel>();
            IReadOnlyList<MstStageEndConditionModel> mstStageEndConditions = new List<MstStageEndConditionModel>();
            IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> mstInGameSpecialRuleUnitStatusModels = new List<MstInGameSpecialRuleUnitStatusModel>();
            if (!selectedStage.SelectedId.IsEmpty())
            {
                var selectedId = selectedStage.SelectedId;
                mstInGameSpecialRules = MstInGameSpecialRuleDataRepository.GetInGameSpecialRuleModels(
                    selectedId,
                    selectedStage.InGameContentType);

                var groupIds = mstInGameSpecialRules
                    .Where(specialRule => specialRule.RuleType == RuleType.UnitStatus)
                    .Select(specialRule => specialRule.RuleValue.ToMasterDataId())
                    .ToList();

                mstInGameSpecialRuleUnitStatusModels = MstInGameSpecialRuleUnitStatusDataRepository
                    .GetInGameSpecialRuleUnitStatusModels(groupIds)
                    .ToList();

                var stageId = selectedStage.InGameType != InGameType.Pvp
                    ? selectedStage.SelectedId
                    : mstInGameModel.MstInGameId;
                mstStageEndConditions = MstStageEndConditionDataRepository
                    .GetMstStageEndConditions(stageId);
            }

            return new StageQuestInitializationResult(
                mstStage,
                mstAdventBattle,
                mstQuest,
                selectedStage,
                mstInGameModel,
                mstInGameSpecialRules,
                mstInGameSpecialRuleUnitStatusModels,
                mstStageEndConditions);
        }
    }
}

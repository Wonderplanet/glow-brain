using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PvpTop.Domain.Resolver;
using Zenject;

namespace GLOW.Core.Domain.Evaluator
{
    public class SelectedStageEvaluator : ISelectedStageEvaluator
    {
        [Inject] ISelectedStageRepository SelectedStageRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IMstCurrentPvpModelResolver MstCurrentPvpModelResolver { get; }

        public SelectedStageModel GetSelectedStage()
        {
            var selectedStageModel = SelectedStageRepository.Get();

            var selectedStageId = MasterDataId.Empty;
            var selectedMstAdventBattleId = MasterDataId.Empty;
            var selectedSysPvpSeasonId = ContentSeasonSystemId.Empty;
            if (!selectedStageModel.SelectedStageId.IsEmpty())
            {
                var mstStageModel = MstStageDataRepository.GetMstStageFirstOrDefault(selectedStageModel.SelectedStageId);
                if (!mstStageModel.IsEmpty())
                {
                    selectedStageId = mstStageModel.Id;
                }
            }
            if (!selectedStageModel.SelectedMstAdventBattleId.IsEmpty())
            {
                var mstAdventBattleModel = MstAdventBattleDataRepository.GetMstAdventBattleModelFirstOrDefault(
                    selectedStageModel.SelectedMstAdventBattleId);
                if (!mstAdventBattleModel.IsEmpty())
                {
                    selectedMstAdventBattleId = mstAdventBattleModel.Id;
                }
            }
            if (!selectedStageModel.SelectedSysPvpSeasonId.IsEmpty())
            {
                var mstPvpBattleModel = MstCurrentPvpModelResolver.CreateMstPvpBattleModel(
                    selectedStageModel.SelectedSysPvpSeasonId);
                if (!mstPvpBattleModel.IsEmpty())
                {
                    selectedSysPvpSeasonId = mstPvpBattleModel.Id == PvpConst.DefaultSysPvpSeasonId
                        ? PvpConst.DefaultSysPvpSeasonId
                        : selectedStageModel.SelectedSysPvpSeasonId;
                }
            }

            return new SelectedStageModel(selectedStageId, selectedMstAdventBattleId, selectedSysPvpSeasonId);
        }
    }
}

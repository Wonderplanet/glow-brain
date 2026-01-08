using System;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Exceptions;
using GLOW.Scenes.BattleResult.Domain.Enum;
using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.BattleResult.Domain.ValueObjects;
using GLOW.Scenes.EventQuestTop.Domain.UseCases;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.UseCases
{
    public class CheckContentOpenUseCase
    {
        [Inject] ISelectedStageEvaluator SelectedStageEvaluator { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstStageEventSettingDataRepository MstStageEventSettingDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public CheckContentOpenModel CheckContentOpenStatus()
        {
            var selectedStageModel = SelectedStageEvaluator.GetSelectedStage();
            switch (selectedStageModel.InGameType)
            {
                case InGameType.Normal:
                    return CreateQuestOpenStatus(selectedStageModel.SelectedStageId);
                case InGameType.AdventBattle:
                    return CreateAdventBattleOpenStatus(selectedStageModel.SelectedMstAdventBattleId);
                case InGameType.Pvp:
                    return CreatePvpOpenStatus(selectedStageModel.SelectedSysPvpSeasonId);
            }
            
            return CheckContentOpenModel.Empty;
        }

        CheckContentOpenModel CreateQuestOpenStatus(MasterDataId selectedStageId)
        {
            var mstNormalStage = MstStageDataRepository.GetMstStageFirstOrDefault(selectedStageId);
            var mstEventStage = MstStageEventSettingDataRepository.GetStageEventSettingFirstOrDefault(selectedStageId);
            if (mstNormalStage.IsEmpty() && mstEventStage.IsEmpty())
            {
                return new CheckContentOpenModel(InGameStageType.NormalStage, InGameStageValidFlag.False);
            }
            
            if (!mstEventStage.IsEmpty())
            {
                return new CheckContentOpenModel(
                    InGameStageType.EventStage, 
                    new InGameStageValidFlag(
                        CalculateTimeCalculator.IsValidTime( 
                            TimeProvider.Now,
                            mstEventStage.StartAt,
                            mstEventStage.EndAt)));
            }
            
            return new CheckContentOpenModel(
                InGameStageType.NormalStage,
                new InGameStageValidFlag(
                    CalculateTimeCalculator.IsValidTime(
                        TimeProvider.Now, 
                        mstNormalStage.StartAt, 
                        mstNormalStage.EndAt)));
        }
        
        CheckContentOpenModel CreateAdventBattleOpenStatus(MasterDataId selectedStageId)
        {
            var mstAdventBattleModel = MstAdventBattleDataRepository.GetMstAdventBattleModelFirstOrDefault(selectedStageId);
            if (mstAdventBattleModel.IsEmpty())
            {
                return new CheckContentOpenModel(InGameStageType.AdventBattle, InGameStageValidFlag.False);
            }
            
            return new CheckContentOpenModel(
                InGameStageType.AdventBattle,
                new InGameStageValidFlag(
                    CalculateTimeCalculator.IsValidTime(
                        TimeProvider.Now, 
                        mstAdventBattleModel.StartDateTime.Value, 
                        mstAdventBattleModel.EndDateTime.Value)));
        }

        CheckContentOpenModel CreatePvpOpenStatus(ContentSeasonSystemId selectedSysPvpSeasonId)
        {
            var sysPvpSeasonModel = GameRepository.GetGameFetchOther().SysPvpSeasonModel;
            if (sysPvpSeasonModel.IsEmpty() || sysPvpSeasonModel.Id != selectedSysPvpSeasonId)
            {
                return new CheckContentOpenModel(InGameStageType.Pvp, InGameStageValidFlag.False);
            }
            
            var pvpStartAt = sysPvpSeasonModel.StartAt;
            var pvpEndAt = sysPvpSeasonModel.EndAt;
            return new CheckContentOpenModel(
                InGameStageType.Pvp,
                new InGameStageValidFlag(
                    CalculateTimeCalculator.IsValidTime(
                        TimeProvider.Now, 
                        pvpStartAt.Value, 
                        pvpEndAt.Value)));
        }
    }
}
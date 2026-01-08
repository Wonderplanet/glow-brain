using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Scenes.AdventBattleMission.Domain.Evaluator
{
    public class AdventBattleDateTimeEvaluator : IAdventBattleDateTimeEvaluator
    {
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        
        [Inject] ITimeProvider TimeProvider { get; }
        
        MstAdventBattleModel IAdventBattleDateTimeEvaluator.GetOpenedAdventBattleModel()
        {
            var mstAdventBattleModels = MstAdventBattleDataRepository.GetMstAdventBattleModels();
            // 開催時間から対象の降臨バトルのマスターデータを取得
            var mstAdventBattleModel = mstAdventBattleModels.FirstOrDefault(
                model => CalculateTimeCalculator.IsValidTime(
                    TimeProvider.Now, 
                    model.StartDateTime.Value,
                    model.EndDateTime.Value),
                MstAdventBattleModel.Empty);
            
            return mstAdventBattleModel;
        }
    }
}
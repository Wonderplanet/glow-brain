using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.PvpBattleResult.Domain.Model;
using GLOW.Scenes.PvpBattleResult.Presentation.ValueObject;
using GLOW.Scenes.PvpBattleResult.Presentation.ViewModel;

namespace GLOW.Scenes.PvpBattleResult.Presentation.Factory
{
    public class PvpBattleResultPointViewModelFactory : IPvpBattleResultPointViewModelFactory
    {
        PvpBattleResultPointViewModel IPvpBattleResultPointViewModelFactory.CreatePvpResultPointViewModel(PvpBattleResultPointModel model)
        {
            var targetModels = model.PvpResultPointRankTargetModels; 
            
            return new PvpBattleResultPointViewModel(
                model.CurrentRankType,
                model.CurrentRankLevel,
                CreatePvpResultPointRankTargetViewModels(targetModels),
                model.VictoryPoint,
                model.OpponentBonusPoint,
                model.TimeBonusPoint,
                model.TotalPoint);
        }
        
        IReadOnlyList<PvpBattleResultPointRankTargetViewModel> CreatePvpResultPointRankTargetViewModels(
            IReadOnlyList<PvpBattleResultPointRankTargetModel> targetModels)
        {
            return targetModels.Select(model => 
            { 
                var beforeScore = model.BeforePoint;
                var targetScore = model.IsDown() 
                        ? PvpPoint.Max(model.AfterPoint, model.TargetRankLowerRequiredPoint) 
                        : PvpPoint.Min(model.AfterPoint, model.TargetRankLowerRequiredPoint);
               
                var beforeGaugeRate = CalculateGaugeRate(
                    beforeScore, 
                    model.BeforeRankLowerRequiredPoint,
                    model.TargetRankLowerRequiredPoint);
                var afterGaugeRate = CalculateGaugeRate(
                    targetScore, 
                    model.BeforeRankLowerRequiredPoint,
                    model.TargetRankLowerRequiredPoint);
                var targetRequiredLowerPoint = model.IsDown()
                    ? model.BeforeRankLowerRequiredPoint
                    : model.TargetRankLowerRequiredPoint;
                
                return new PvpBattleResultPointRankTargetViewModel(
                    model.BeforePoint,
                    model.AfterPoint,
                    targetRequiredLowerPoint,
                    model.TargetRankType,
                    model.TargetScoreRankLevel,
                    beforeGaugeRate,
                    afterGaugeRate);
            })
            .ToList();
        }
        
        PvpBattleResultRankAnimationGaugeRate CalculateGaugeRate(
            PvpPoint currentTotalPoint,
            PvpPoint beforeRequiredLowerPoint,
            PvpPoint targetRequiredLowerPoint)
        {
            if (beforeRequiredLowerPoint <= targetRequiredLowerPoint)
            {
                return CalculateUppedGaugeRate(
                    currentTotalPoint,
                    beforeRequiredLowerPoint,
                    targetRequiredLowerPoint);
            }
            else
            {
                return CalculateDownedGaugeRate(
                    currentTotalPoint,
                    beforeRequiredLowerPoint,
                    targetRequiredLowerPoint);
            }
        }

        PvpBattleResultRankAnimationGaugeRate CalculateUppedGaugeRate(
            PvpPoint currentTotalPoint,
            PvpPoint beforeRequiredLowerPoint,
            PvpPoint targetRequiredLowerPoint)
        {
            if (targetRequiredLowerPoint.IsEmpty())
            {
                return PvpBattleResultRankAnimationGaugeRate.One;
            }

            float currentTotalPointValue = (currentTotalPoint - beforeRequiredLowerPoint).Value;
            float targetRequiredLowerPointValue = (targetRequiredLowerPoint - beforeRequiredLowerPoint).Value;
            if (targetRequiredLowerPointValue == 0)
            {
                // 0除算を防ぐため、0の場合は1を返す
                return PvpBattleResultRankAnimationGaugeRate.One;
            }
            
            var gaugeRate = new PvpBattleResultRankAnimationGaugeRate(currentTotalPointValue / targetRequiredLowerPointValue);
            return gaugeRate;
        }
        
        PvpBattleResultRankAnimationGaugeRate CalculateDownedGaugeRate(
            PvpPoint currentTotalPoint,
            PvpPoint beforeRequiredLowerPoint,
            PvpPoint targetRequiredLowerPoint)
        {
            if (targetRequiredLowerPoint.IsEmpty())
            {
                return PvpBattleResultRankAnimationGaugeRate.Zero;
            }
            
            float currentTotalPointValue = (beforeRequiredLowerPoint - currentTotalPoint).Value;
            float targetRequiredLowerPointValue = (beforeRequiredLowerPoint - targetRequiredLowerPoint).Value;
            
            if (targetRequiredLowerPointValue == 0)
            {
                // 0除算を防ぐため、0の場合は1を返す
                return PvpBattleResultRankAnimationGaugeRate.Zero;
            }
            
            var gaugeRate = new PvpBattleResultRankAnimationGaugeRate(1 - currentTotalPointValue / targetRequiredLowerPointValue);
            return gaugeRate;
        }
    }
}
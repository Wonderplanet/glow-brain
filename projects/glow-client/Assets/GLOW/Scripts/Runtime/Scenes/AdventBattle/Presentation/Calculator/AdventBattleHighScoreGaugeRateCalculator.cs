using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Extensions;
using GLOW.Scenes.AdventBattle.Domain.Model;
using GLOW.Scenes.AdventBattle.Presentation.Calculator.Model;
using GLOW.Scenes.AdventBattle.Presentation.ValueObject;

namespace GLOW.Scenes.AdventBattle.Presentation.Calculator
{
    public class AdventBattleHighScoreGaugeRateCalculator : IAdventBattleHighScoreGaugeRateCalculator
    {
        AdventBattleHighScoreGaugeModel IAdventBattleHighScoreGaugeRateCalculator.CalculateHighScoreGaugeRate(
            IReadOnlyList<AdventBattleHighScoreRewardModel> highScoreRewards,
            AdventBattleScore currentMaxScore,
            AdventBattleScore maxScoreLastAnimationPlayed)
        {
            // 明示的にポイント順で昇順ソートする
            highScoreRewards = highScoreRewards.OrderBy(
                reward => reward.AdventBattleHighScore).ToList();

            if (highScoreRewards.Count <= 1) return AdventBattleHighScoreGaugeModel.Empty;

            // 報酬と報酬の間の割合
            var interval = 1.0f / (highScoreRewards.Count - 1);
            var rewardIntervalGaugeRate = new AdventBattleHighScoreGaugeRate(interval);

            // 前回のアニメーション再生時の最高スコアに達した報酬の数
            var beforeAchieveAndNotAchieveIndex = highScoreRewards.FindIndex(
                reward => reward.AdventBattleHighScore > maxScoreLastAnimationPlayed);
            
            // アニメーション再生後の最高スコアに達した報酬の数
            var afterAchieveAndNotAchieveIndex = highScoreRewards.FindIndex(reward => reward.AdventBattleHighScore > currentMaxScore);

            // 報酬の数を比較して値が異なっていれば報酬獲得している
            var isReceivableReward = beforeAchieveAndNotAchieveIndex != afterAchieveAndNotAchieveIndex;
            
            AdventBattleHighScoreGaugeRate currentGaugeRate;
            if (beforeAchieveAndNotAchieveIndex == 0)
            {
                currentGaugeRate = AdventBattleHighScoreGaugeRate.Zero;
            }
            else
            {
                if (beforeAchieveAndNotAchieveIndex < 0)
                {
                    beforeAchieveAndNotAchieveIndex = highScoreRewards.Count  - 1;
                }
                currentGaugeRate = rewardIntervalGaugeRate * (beforeAchieveAndNotAchieveIndex - 1);
                var maxAchievedScore = (float)highScoreRewards[beforeAchieveAndNotAchieveIndex - 1].AdventBattleHighScore.Value;
                var minNotAchievedScore = (float)highScoreRewards[beforeAchieveAndNotAchieveIndex].AdventBattleHighScore.Value;
                var addRate = rewardIntervalGaugeRate * ((maxScoreLastAnimationPlayed.Value - maxAchievedScore) /  (minNotAchievedScore - maxAchievedScore));
                currentGaugeRate += addRate;
            }
            
            AdventBattleHighScoreGaugeRate nextGaugeRate;
            AdventBattleHighScoreRewardObtainedFlag isLastHighScoreRewardObtained = AdventBattleHighScoreRewardObtainedFlag.False;
            if (afterAchieveAndNotAchieveIndex == 0)
            {
                nextGaugeRate = AdventBattleHighScoreGaugeRate.Zero;
            }
            else
            {
                if (afterAchieveAndNotAchieveIndex < 0)
                {
                    // 報酬を全て獲得している場合
                    afterAchieveAndNotAchieveIndex = highScoreRewards.Count - 1;
                    isLastHighScoreRewardObtained = AdventBattleHighScoreRewardObtainedFlag.True;
                }
                nextGaugeRate = rewardIntervalGaugeRate * (afterAchieveAndNotAchieveIndex - 1);
                var maxAchievedScore = (float)highScoreRewards[afterAchieveAndNotAchieveIndex - 1].AdventBattleHighScore.Value;
                var minNotAchievedScore = (float)highScoreRewards[afterAchieveAndNotAchieveIndex].AdventBattleHighScore.Value;
                var addRate = rewardIntervalGaugeRate * ((currentMaxScore.Value - maxAchievedScore) /  (minNotAchievedScore - maxAchievedScore));
                nextGaugeRate += addRate;
            }

            var minHighScoreReward = highScoreRewards.FirstOrDefault(AdventBattleHighScoreRewardModel.Empty);
            if (currentMaxScore < minHighScoreReward.AdventBattleHighScore)
            {
                return new AdventBattleHighScoreGaugeModel(
                    currentGaugeRate,
                    new List<AdventBattleHighScoreGaugeRateElementModel>());
            }

            // 同一だったら演出なし
            if (currentMaxScore <= maxScoreLastAnimationPlayed)
            {
                return new AdventBattleHighScoreGaugeModel(
                    currentGaugeRate,
                    new List<AdventBattleHighScoreGaugeRateElementModel>());
            }

            // 演出後に受け取る報酬の数
            var rewardGaugeRateList = new List<AdventBattleHighScoreGaugeRateElementModel>();
            if (!isReceivableReward)
            {
                // ゲージが伸びる演出だけは行う
                var element = new AdventBattleHighScoreGaugeRateElementModel(
                    HighScoreRewardCellIndex.Empty, 
                    nextGaugeRate, 
                    AdventBattleHighScoreRewardObtainedFlag.False);
                rewardGaugeRateList.Add(element);
            }
            else
            {
                var startCount = beforeAchieveAndNotAchieveIndex;
                var endCount = afterAchieveAndNotAchieveIndex;
                for (var i = startCount; i < endCount; i++)
                {
                    var endRate = rewardIntervalGaugeRate * i;
                    var element = new AdventBattleHighScoreGaugeRateElementModel(
                        new HighScoreRewardCellIndex(i), 
                        endRate, 
                        AdventBattleHighScoreRewardObtainedFlag.True);
                    rewardGaugeRateList.Add(element);
                }
                
                var finishRate = nextGaugeRate;
                var finishElement = new AdventBattleHighScoreGaugeRateElementModel(
                    new HighScoreRewardCellIndex(endCount), 
                    finishRate, 
                    isLastHighScoreRewardObtained);
                rewardGaugeRateList.Add(finishElement);
            }

            return new AdventBattleHighScoreGaugeModel(
                currentGaugeRate,
                rewardGaugeRateList);
        }
    }
}

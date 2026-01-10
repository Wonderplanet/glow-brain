using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public class AdventBattleScoreModelFactory : IAdventBattleScoreModelFactory
    {
        [Inject] IInGameScene InGameScene { get; }

        public ResultScoreModel CreateAdventBattleScoreModel(
            UserAdventBattleModel prevAdventBattleModel,
            EventBonusPercentage eventBonusPercentage)
        {
            var currentScore = InGameScene.ScoreModel.TotalScore;
            var currentHighScore = prevAdventBattleModel.MaxScore.ToInGameScore;
            var isNewRecord = currentScore > currentHighScore;
            
            ResultScoreModel resultScoreModel = new ResultScoreModel(
                currentScore,
                isNewRecord ? currentScore : currentHighScore,
                new NewRecordFlag(isNewRecord),
                eventBonusPercentage);

            return resultScoreModel;
        }
    }
}
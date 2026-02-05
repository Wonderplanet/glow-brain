using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle.ScoreCalculator;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class ScoreCalculateModelFactory : IScoreCalculateModelFactory
    {
        public ScoreCalculateModel Create(
            InGameType type,
            QuestType questType,
            AdventBattleScoreAdditionModel scoreAdditionModel)
        {
            if (type == InGameType.AdventBattle)
            {
                // ダメージスコア
                var scoreCalculateType = scoreAdditionModel.Type.ToScoreCalculateType();
                var elements =  scoreCalculateType switch
                {
                    ScoreCalculateType.AllEnemyUnitsAndOutPost => new List<IInGameScoreCalculator>
                    {
                        new AllEnemyUnitsDamageScoreCalculator(),
                        new EnemyOutpostDamageScoreCalculator()
                    },
                    ScoreCalculateType.AllEnemyUnits => new List<IInGameScoreCalculator>
                    {
                        new AllEnemyUnitsDamageScoreCalculator()
                    },
                    ScoreCalculateType.BossEnemyUnits => new List<IInGameScoreCalculator>
                    {
                        new BossEnemyDamageScoreCalculator()
                    },
                    ScoreCalculateType.EnemyOutpost => new List<IInGameScoreCalculator>
                    {
                        new EnemyOutpostDamageScoreCalculator()
                    },
                    _ => new List<IInGameScoreCalculator>()
                };

                // 撃破スコア
                elements.Add(new BossEnemyDefeatScoreCalculator());
                elements.Add(new EnemyExceptBossDefeatScoreCalculator());

                return new ScoreCalculateModel(elements, scoreAdditionModel.DamageScoreAdditionalCoef);
            }

            if (questType == QuestType.Enhance)
            {
                return new ScoreCalculateModel(
                    new List<IInGameScoreCalculator>{ new EnemyOutpostDamageScoreCalculator() },
                    DamageScoreAdditionalCoef.One);
            }

            return new ScoreCalculateModel(
                new List<IInGameScoreCalculator>(),
                DamageScoreAdditionalCoef.One);
        }
    }
}

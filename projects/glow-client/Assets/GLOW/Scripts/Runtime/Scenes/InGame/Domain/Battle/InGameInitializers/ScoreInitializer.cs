using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public class ScoreInitializer : IScoreInitializer
    {
        InGameScoreModel IScoreInitializer.InitializeScore(QuestType questType)
        {
            // コインクエストだけはスコア演出表示を行わない
            var isScoreEffectVisible = questType != QuestType.Enhance ?
                ScoreEffectVisibleFlag.True : ScoreEffectVisibleFlag.False;
            return new InGameScoreModel(
                new Dictionary<InGameScoreType, InGameScore>(),
                isScoreEffectVisible);
        }
    }
}

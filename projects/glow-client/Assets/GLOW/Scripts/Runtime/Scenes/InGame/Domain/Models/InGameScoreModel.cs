using System.Collections.Generic;
using System.Linq;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record InGameScoreModel(
        IReadOnlyDictionary<InGameScoreType, InGameScore> ScoreDictionary,
        ScoreEffectVisibleFlag IsScoreEffectVisible)
    {
        public static InGameScoreModel Empty { get; } = new InGameScoreModel(
            new Dictionary<InGameScoreType, InGameScore>(),
            ScoreEffectVisibleFlag.Empty
        );

        public InGameScore TotalScore => new InGameScore(ScoreDictionary.Values.Sum(score => score.Value));

        public InGameScoreModel AddScore(IReadOnlyList<ScoreCalculationResultModel> additionalScores)
        {
            var newScoreDictionary = new Dictionary<InGameScoreType, InGameScore>(ScoreDictionary);

            foreach (var score in additionalScores)
            {
                if (newScoreDictionary.ContainsKey(score.ScoreType))
                {
                    newScoreDictionary[score.ScoreType] += score.Score;
                }
                else
                {
                    newScoreDictionary[score.ScoreType] = score.Score;
                }
            }

            return this with
            {
                ScoreDictionary = newScoreDictionary
            };
        }

        public InGameScore GetScoreByType(InGameScoreType type)
        {
             return ScoreDictionary.TryGetValue(type, out var score) ? score : InGameScore.Empty;
        }
    }
}

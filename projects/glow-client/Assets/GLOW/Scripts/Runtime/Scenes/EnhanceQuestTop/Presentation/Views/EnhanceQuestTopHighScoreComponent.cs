using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.EnhanceQuestTop.Presentation.Views
{
    public class EnhanceQuestTopHighScoreComponent : UIObject
    {
        [SerializeField] UIText _highScoreText;
        [SerializeField] UIText _nextRequireScoreText;
        [SerializeField] UIText _nextRewardText;

        public void Setup(EnhanceQuestScore highScore, EnhanceQuestMinThresholdScore nextRequireScore, ItemAmount nextReward)
        {
            _highScoreText.SetText("{0} pt", highScore.ToString());
            _nextRequireScoreText.SetText("{0} pt", nextRequireScore.ToString());
            _nextRewardText.SetText("{0}", nextReward.ToStringSeparated());
        }
    }
}

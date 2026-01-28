using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class InGameScoreComponent : UIObject
    {
        [SerializeField] UIText _scoreText;

        public void Initialize()
        {
            _scoreText.SetText("{0} pt", InGameScore.Zero);
        }

        public void SetScore(InGameScore score)
        {
            _scoreText.SetText("{0} pt", score);
        }
    }
}
